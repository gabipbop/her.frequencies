<?php

namespace App\Controller\Admin;

use App\Entity\Artist;
use App\Entity\Link;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ArtistCrudController extends AbstractCrudController
{
    public function __construct(
        private AdminUrlGenerator $adminUrlGenerator,
        private EntityManagerInterface $em,
    ) {}

    public static function getEntityFqcn(): string
    {
        return Artist::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Artist')
            ->setEntityLabelInPlural('Artists')
            ->setDefaultSort(['acceptedAt' => 'DESC'])
            ->showEntityActionsInlined();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('name', 'Name'))
            ->add(TextFilter::new('country', 'Country'))
            ->add(TextFilter::new('city', 'City'))
            ->add(EntityFilter::new('categories', 'Category'));
    }

    public function configureActions(Actions $actions): Actions
    {
        $addLink = Action::new('addLink', 'Add link', 'fas fa-plus')
            ->linkToCrudAction('addLinkAction')
            ->setCssClass('btn btn-outline-primary btn-sm');

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_DETAIL, $addLink)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, fn(Action $a) => $a->setLabel('View')->setIcon('fas fa-eye'))
            ->update(Crud::PAGE_INDEX, Action::EDIT, fn(Action $a) => $a->setLabel('Edit')->setIcon('fas fa-pen'))
            ->update(Crud::PAGE_INDEX, Action::DELETE, fn(Action $a) => $a->setLabel('Delete')->setIcon('fas fa-trash'))
            ->update(Crud::PAGE_INDEX, Action::NEW, fn(Action $a) => $a->setLabel('Add artist'))
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER);
    }

    public function configureFields(string $pageName): iterable
    {
        $isForm  = in_array($pageName, [Crud::PAGE_NEW, Crud::PAGE_EDIT]);
        $isIndex = $pageName === Crud::PAGE_INDEX;

        if ($isIndex) {
            yield ImageField::new('picture', 'Photo')
                ->setBasePath('/uploads/artists')
                ->setUploadDir('public/uploads/artists')
                ->setRequired(false);
            yield TextField::new('name', 'Name');
            yield TextField::new('country', 'Country');
            yield TextField::new('city', 'City');
            yield AssociationField::new('categories', 'Categories')
                ->formatValue(fn($v, Artist $e) => implode(', ', $e->getCategories()->map(fn($c) => $c->getName())->toArray()));
            yield DateTimeField::new('acceptedAt', 'Accepted at')->setFormat('d MMM yyyy');
            return;
        }

        if ($isForm) {
            yield FormField::addFieldset('General info');
            yield TextField::new('name', 'Name');
            yield TextField::new('country', 'Country');
            yield TextField::new('city', 'City');
            yield TextareaField::new('description', 'Description');

            yield FormField::addFieldset('Media');
            yield ImageField::new('picture', 'Picture')
                ->setBasePath('/uploads/artists')
                ->setUploadDir('public/uploads/artists')
                ->setRequired(false);

            yield FormField::addFieldset('Categories');
            yield AssociationField::new('categories', 'Categories');
            return;
        }

        // Detail page
        yield FormField::addFieldset('Artist profile');
        yield IdField::new('id');
        yield TextField::new('name', 'Name');
        yield TextField::new('country', 'Country');
        yield TextField::new('city', 'City');
        yield DateTimeField::new('acceptedAt', 'Accepted at')->setFormat('d MMM yyyy, HH:mm');

        yield FormField::addFieldset('Photo');
        yield ImageField::new('picture', 'Picture')
            ->setBasePath('/uploads/artists')
            ->setUploadDir('public/uploads/artists')
            ->setRequired(false);

        yield FormField::addFieldset('Description');
        yield TextareaField::new('description', 'Description')->renderAsHtml()->setNumOfRows(6);

        yield FormField::addFieldset('Categories');
        yield AssociationField::new('categories', 'Categories')
            ->renderAsHtml()
            ->formatValue(fn($v, Artist $e) => implode(' &nbsp;·&nbsp; ', $e->getCategories()->map(fn($c) => $c->getName())->toArray()) ?: '—');

        yield FormField::addFieldset('Links');
        yield AssociationField::new('links', 'Links')
            ->renderAsHtml()
            ->formatValue(function ($v, Artist $entity) {
                $html = '';
                foreach ($entity->getLinks() as $link) {
                    $removeUrl = $this->adminUrlGenerator
                        ->setController(self::class)
                        ->setAction('removeLinkAction')
                        ->setEntityId($entity->getId())
                        ->set('linkId', $link->getId())
                        ->generateUrl();
                    $html .= sprintf(
                        '<div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
                            <a href="%s" target="_blank" rel="noopener">%s ↗</a>
                            <a href="%s"
                               onclick="return confirm(\'Remove %s?\')"
                               style="color:#dc3545;font-size:12px;text-decoration:none;"
                               title="Remove">✕ remove</a>
                        </div>',
                        htmlspecialchars($link->getUrl()),
                        htmlspecialchars($link->getName()),
                        $removeUrl,
                        htmlspecialchars($link->getName())
                    );
                }
                return $html ?: '<span style="color:#999">No links yet — use "Add link" above.</span>';
            });
    }

    #[AdminRoute]
    public function addLinkAction(Request $request): Response
    {
        $artist = $this->getContext()->getEntity()->getInstance();

        if ($request->isMethod('POST')) {
            $name = trim($request->request->get('link_name', ''));
            $url  = trim($request->request->get('link_url', ''));

            if ($name !== '' && $url !== '') {
                $link = new Link();
                $link->setName($name);
                $link->setUrl($url);
                $this->em->persist($link);
                $artist->addLink($link);
                $this->em->flush();
                $this->addFlash('success', "Link \"{$name}\" added.");
            } else {
                $this->addFlash('warning', 'Please fill in both fields.');
            }

            return $this->redirect(
                $this->adminUrlGenerator->setController(self::class)->setAction(Action::DETAIL)->setEntityId($artist->getId())->generateUrl()
            );
        }

        return $this->render('admin/artist/add_link.html.twig', [
            'artist'   => $artist,
            'back_url' => $this->adminUrlGenerator->setController(self::class)->setAction(Action::DETAIL)->setEntityId($artist->getId())->generateUrl(),
        ]);
    }

    #[AdminRoute]
    public function removeLinkAction(Request $request): Response
    {
        $artist = $this->getContext()->getEntity()->getInstance();
        $linkId = (int) $request->query->get('linkId');
        $link   = $this->em->find(Link::class, $linkId);

        if ($link && $artist->getLinks()->contains($link)) {
            $artist->removeLink($link);
            $this->em->flush();
            $this->addFlash('success', 'Link removed.');
        }

        return $this->redirect(
            $this->adminUrlGenerator->setController(self::class)->setAction(Action::DETAIL)->setEntityId($artist->getId())->generateUrl()
        );
    }
}
