<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use JMS\JobQueueBundle\Entity\Job;

class JobCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Job::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Job')
            ->setEntityLabelInPlural('Job')
            ->setSearchFields(['id', 'state', 'queue', 'priority', 'workerName', 'command', 'args', 'output', 'errorOutput', 'exitCode', 'maxRuntime', 'maxRetries', 'stackTrace', 'runtime', 'memoryUsage', 'memoryUsageReal']);
    }

    public function configureFields(string $pageName): iterable
    {
        $id = TextField::new('id', 'ID');
        $command = TextField::new('command');
        $args = TextField::new('args');
        $state = TextField::new('state');
        $output = TextareaField::new('output');
        $errorOutput = TextareaField::new('errorOutput');
        $queue = TextField::new('queue');
        $priority = IntegerField::new('priority');
        $createdAt = DateTimeField::new('createdAt');
        $startedAt = DateTimeField::new('startedAt');
        $checkedAt = DateTimeField::new('checkedAt');
        $workerName = TextField::new('workerName');
        $executeAfter = DateTimeField::new('executeAfter');
        $closedAt = DateTimeField::new('closedAt');
        $exitCode = IntegerField::new('exitCode');
        $maxRuntime = IntegerField::new('maxRuntime');
        $maxRetries = IntegerField::new('maxRetries');
        $stackTrace = Field::new('stackTrace');
        $runtime = IntegerField::new('runtime');
        $memoryUsage = IntegerField::new('memoryUsage');
        $memoryUsageReal = IntegerField::new('memoryUsageReal');
        $dependencies = AssociationField::new('dependencies');
        $originalJob = AssociationField::new('originalJob');
        $retryJobs = AssociationField::new('retryJobs');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $command, $args, $state, $output, $errorOutput];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $state, $queue, $priority, $createdAt, $startedAt, $checkedAt, $workerName, $executeAfter, $closedAt, $command, $args, $output, $errorOutput, $exitCode, $maxRuntime, $maxRetries, $stackTrace, $runtime, $memoryUsage, $memoryUsageReal, $dependencies, $originalJob, $retryJobs];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$id, $command, $args, $state, $output, $errorOutput];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$id, $command, $args, $state, $output, $errorOutput];
        }
    }
}
