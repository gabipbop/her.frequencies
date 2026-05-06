<?php

namespace App\Controller\Admin;

use App\Entity\ArtistRequest;
use App\Enum\ArtistRequestStatus;
use App\Form\RawLinkType;
use App\Repository\CategoryRepository;
use App\Service\ArtistRequestApprover;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ArtistRequestCrudController extends AbstractCrudController
{
    public function __construct(
        private ArtistRequestApprover $approver,
        private AdminUrlGenerator $adminUrlGenerator,
        private CategoryRepository $categoryRepository,
    ) {}

    public static function getEntityFqcn(): string
    {
        return ArtistRequest::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Artist Request')
            ->setEntityLabelInPlural('Artist Requests')
            ->setDefaultSort(['created_at' => 'DESC'])
            ->setPageTitle(Crud::PAGE_NEW, 'New artist request')
            ->setPageTitle(Crud::PAGE_EDIT, 'Edit artist request');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add(
            ChoiceFilter::new('status', 'Status')->setChoices([
                'Pending'  => ArtistRequestStatus::PENDING->value,
                'Approved' => ArtistRequestStatus::APPROVED->value,
                'Rejected' => ArtistRequestStatus::REJECTED->value,
            ])
        );
    }

    public function configureFields(string $pageName): iterable
    {
        $categoryChoices = [];
        foreach ($this->categoryRepository->findBy([], ['name' => 'ASC']) as $category) {
            $categoryChoices[$category->getName()] = $category->getId();
        }

        yield IdField::new('id')->hideOnForm();

        yield TextField::new('name', 'Name');
        yield TextField::new('country', 'Country');
        yield TextField::new('city', 'City');
        yield EmailField::new('email', 'Email');

        yield TextareaField::new('description', 'Description')->hideOnIndex();

        yield ImageField::new('picture', 'Picture')
            ->setBasePath('/uploads/artist_requests')
            ->setUploadDir('public/uploads/artist_requests')
            ->setRequired(false)
            ->hideOnIndex();

        // Formulaire : multi-select des catégories
        yield ChoiceField::new('rawCategories', 'Categories')
            ->setChoices($categoryChoices)
            ->allowMultipleChoices()
            ->renderExpanded(false)
            ->onlyOnForms();

        // Détail : noms des catégories en texte
        yield TextField::new('rawCategories', 'Categories')
            ->onlyOnDetail()
            ->renderAsHtml()
            ->formatValue(function ($value) use ($categoryChoices) {
                if (empty($value)) return '—';
                $names = array_keys(array_filter($categoryChoices, fn($id) => in_array($id, (array) $value)));
                return implode(' &nbsp;·&nbsp; ', $names) ?: '—';
            });

        // Formulaire : collection de liens
        yield CollectionField::new('rawLinks', 'Links')
            ->setEntryType(RawLinkType::class)
            ->allowAdd()
            ->allowDelete()
            ->onlyOnForms();

        // Détail : liens cliquables
        yield TextField::new('rawLinks', 'Links')
            ->onlyOnDetail()
            ->renderAsHtml()
            ->formatValue(function ($value) {
                if (empty($value)) return '—';
                $html = '';
                foreach ((array) $value as $link) {
                    $name = htmlspecialchars($link['name'] ?? '');
                    $url  = htmlspecialchars($link['url'] ?? '');
                    if ($url) {
                        $html .= sprintf(
                            '<a href="%s" target="_blank" rel="noopener" style="display:inline-block;margin-right:12px;">%s ↗</a>',
                            $url, $name ?: $url
                        );
                    }
                }
                return $html ?: '—';
            });

        yield ChoiceField::new('status', 'Status')
            ->setChoices([
                'Pending'  => ArtistRequestStatus::PENDING->value,
                'Approved' => ArtistRequestStatus::APPROVED->value,
                'Rejected' => ArtistRequestStatus::REJECTED->value,
            ])
            ->renderAsBadges([
                ArtistRequestStatus::PENDING->value  => 'warning',
                ArtistRequestStatus::APPROVED->value => 'success',
                ArtistRequestStatus::REJECTED->value => 'danger',
            ])
            ->hideOnForm();

        yield DateTimeField::new('created_at', 'Submitted at')->hideOnForm();
    }

    public function configureActions(Actions $actions): Actions
    {
        $isPending = fn(ArtistRequest $r) => $r->getStatus() === ArtistRequestStatus::PENDING;

        $approve = Action::new('approve', 'Approve', 'fas fa-check')
            ->linkToCrudAction('approveAction')
            ->setCssClass('btn btn-success')
            ->displayIf($isPending);

        $reject = Action::new('reject', 'Reject', 'fas fa-times')
            ->linkToCrudAction('rejectAction')
            ->setCssClass('btn btn-danger')
            ->displayIf($isPending);

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $approve)
            ->add(Crud::PAGE_INDEX, $reject)
            ->add(Crud::PAGE_DETAIL, $approve)
            ->add(Crud::PAGE_DETAIL, $reject)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, fn(Action $a) => $a->setLabel('View')->setIcon('fas fa-eye'))
            ->update(Crud::PAGE_INDEX, Action::EDIT, fn(Action $a) => $a->displayIf($isPending))
            ->update(Crud::PAGE_INDEX, Action::DELETE, fn(Action $a) => $a->displayIf($isPending))
            ->update(Crud::PAGE_DETAIL, Action::EDIT, fn(Action $a) => $a->displayIf($isPending))
            ->update(Crud::PAGE_DETAIL, Action::DELETE, fn(Action $a) => $a->displayIf($isPending))
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER);
    }

    public function createEntity(string $entityFqcn): ArtistRequest
    {
        $request = new ArtistRequest();
        $request->setStatus(ArtistRequestStatus::PENDING);
        $request->setCreatedAt(new \DateTimeImmutable());

        return $request;
    }

    #[AdminRoute]
    public function approveAction(): RedirectResponse
    {
        $request = $this->getContext()->getEntity()->getInstance();
        $this->approver->approve($request);
        $this->addFlash('success', 'Artist approved!');

        return $this->redirect(
            $this->adminUrlGenerator->setController(self::class)->setAction(Action::INDEX)->generateUrl()
        );
    }

    #[AdminRoute]
    public function rejectAction(): RedirectResponse
    {
        $request = $this->getContext()->getEntity()->getInstance();
        $this->approver->reject($request);
        $this->addFlash('success', 'Request rejected.');

        return $this->redirect(
            $this->adminUrlGenerator->setController(self::class)->setAction(Action::INDEX)->generateUrl()
        );
    }
}
