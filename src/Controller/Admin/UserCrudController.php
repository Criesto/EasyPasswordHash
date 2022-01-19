<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('firstName', 'Imię'),
            TextField::new('lastName', 'Nazwisko'),
            TextField::new('username', 'Alias'),
            ChoiceField::new('roles', "Role użytkownika")
                ->onlyOnForms()
                ->allowMultipleChoices()
                ->autocomplete()
                ->setChoices([
                    'Nikt ;)' => null,
                    'Administrator' => 'ROLE_ADMIN',
                    'Użytkownik' => 'ROLE_USER'])
        ,
            TextField::new('password', 'Hasło')
                ->onlyWhenCreating()
                ->setFormType(PasswordType::class)
            ,
            EmailField::new('email', 'e-mail'),
            BooleanField::new('isVerified', 'Potwierdzony')
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Użytkownik')
            ->setEntityLabelInPlural('Użytkownicy')
            ->setSearchFields(['firstName', 'lastName'])
            ->setDefaultSort(['id' => 'ASC'])
            ->setDateFormat('yyyy-MM-dd')
            ->setNumberFormat('%.2d')
            ->setPageTitle('new', 'Nowy użytkownik')
            ->setPageTitle('detail', 'Profil użytkownika')
            ->setPageTitle('edit', 'Edycja użytkownika')
            ->setPageTitle('index', 'Lista użytkowników')
            ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('firstName', 'Imię'))
            ->add(TextFilter::new('lastName', 'Nazwisko'))
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::SAVE_AND_ADD_ANOTHER)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action
                    ->setIcon('fas fa-user-plus')
                    ->setLabel('Dodaj nowego użytkownika')
                    ;})
            ;
    }
}
