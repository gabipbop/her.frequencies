<?php

namespace App\Controller\Admin;

use App\Entity\ArtistRequest;
use App\Service\ArtistRequestApprover;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ArtistRequestCrudController extends AbstractCrudController
{
    public function __construct(
        private ArtistRequestApprover $approver,
        private AdminUrlGenerator $adminUrlGenerator,
    ) {}

    public static function getEntityFqcn(): string
    {
        return ArtistRequest::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Nom'),
            TextField::new('country', 'Pays'),
            TextField::new('city', 'Ville'),
            TextareaField::new('description')->hideOnIndex(),
            EmailField::new('email'),
            ImageField::new('picture', 'Photo')
                ->setBasePath('/uploads/artist_requests')
                ->setRequired(false)
                ->hideOnForm(),
            ChoiceField::new('status', 'Statut')
                ->setChoices([
                    'Draft' => 'draft',
                    'Pending' => 'pending',
                    'Approved' => 'approved',
                    'Rejected' => 'rejected',
                ])
                ->hideOnForm(),
            ArrayField::new('rawCategories', 'Catégories')->hideOnIndex(),
            ArrayField::new('rawLinks', 'Liens')->hideOnIndex(),
            DateTimeField::new('created_at', 'Créé le')->hideOnForm(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $approve = Action::new('approve', 'Approuver', 'fas fa-check')
            ->linkToCrudAction('approveAction')
            ->setCssClass('btn btn-success');

        $reject = Action::new('reject', 'Rejeter', 'fas fa-times')
            ->linkToCrudAction('rejectAction')
            ->setCssClass('btn btn-danger');

        return $actions
            ->add(Crud::PAGE_INDEX, $approve)
            ->add(Crud::PAGE_INDEX, $reject)
            ->add(Crud::PAGE_DETAIL, $approve)
            ->add(Crud::PAGE_DETAIL, $reject);
    }

    #[AdminRoute]
    public function approveAction(): RedirectResponse
    {
        $request = $this->getContext()->getEntity()->getInstance();
        $this->approver->approve($request);
        $this->addFlash('success', 'Artiste approuvé !');

        $url = $this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl();

        return $this->redirect($url);
    }

    #[AdminRoute]
    public function rejectAction(): RedirectResponse
    {
        $request = $this->getContext()->getEntity()->getInstance();
        $this->approver->reject($request);
        $this->addFlash('success', 'Demande rejetée !');

        $url = $this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl();

        return $this->redirect($url);
    }
}
