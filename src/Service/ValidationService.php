<?php


namespace App\Service;

use App\Exception\ApiException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidationService
{
    /**
     * @var string
     */
    private $env;
    /**
     * @var Request|null
     */
    private $request;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(string $env, ValidatorInterface $validator, RequestStack $requestStack)
    {
        $this->env = $env;
        $this->validator = $validator;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * Valide un objet en utilisant la configuration des converters
     * @param $subject
     */
    public function validate($subject): void
    {
        $options = $this->getParamConverterOptions($subject);
        $validatorOptions = $this->getValidatorOptions($options);

        $errors = $this->validator->validate($subject, null, $validatorOptions['groups']);
        $this->handleErrors($errors);
    }

    /**
     * Récupère la configuration de validation pour l'objet s'il est désérialisé
     * @param $subject
     * @return array|mixed
     */
    public function getParamConverterOptions($subject)
    {
        $class = get_class($subject);
        if ($configuration = $this->request->attributes->get('_converters')) {
            foreach (is_array($configuration) ? $configuration : [$configuration] as $configuration) {
                if ($class === $configuration->getClass()) {
                    return $configuration->getOptions();
                }
            }
        }
        return [];
    }

    /**
     * Récupère une configuration de validation
     * @param array $options
     *
     * @return array
     */
    private function getValidatorOptions(array $options): array
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'groups' => null,
            'traverse' => false,
            'deep' => false,
        ]);

        return $resolver->resolve($options['validator'] ?? []);
    }

    /**
     * Gère les erreurs et throw des exceptions
     * @param ConstraintViolationListInterface|null $validationErrors
     */
    public function handleErrors(ConstraintViolationListInterface $validationErrors = null): void
    {
        if ($validationErrors && count($validationErrors) > 0) {
            $messages = [];
            foreach ($validationErrors as $oConstraintViolation) {
                /**
                 * @var ConstraintViolation $oConstraintViolation
                 */
                $messages[] = $oConstraintViolation->getPropertyPath() . ' - ' . $oConstraintViolation->getMessage();
            }
            throw new ApiException(implode('; ', $messages), Response::HTTP_BAD_REQUEST);
        }
    }
}
