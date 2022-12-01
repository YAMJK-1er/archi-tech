<?php

namespace App\Controller\Admin;

use App\Entity\Approvisionnement;
use App\Entity\Element;
use App\Entity\Images;
use App\Entity\Observation;
use App\Entity\Planning;
use App\Entity\Projet;
use App\Entity\Tache;
use App\Entity\TauxExecFin;
use App\Entity\TauxExecPhys;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use PhpParser\Builder\Method;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    public function __construct(private AdminUrlGenerator $adminUrlGenerator)
    {
        
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $url = $this->adminUrlGenerator->setController(UserCrudController::class)->generateUrl() ;

        return $this->redirect($url);

    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Suivi Chantier');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::section('User');
        yield MenuItem::linkToCrud('User', 'fas fa-user', User::class);

        yield MenuItem::section('Projets');
        yield MenuItem::linkToCrud('Projets' , 'fas fa-building' , Projet::class);
        yield MenuItem::linkToCrud('Taux d\'exécution financière' , 'fas fa-edit' , TauxExecFin::class);
        yield MenuItem::linkToCrud('Taux d\'exéccution physique' , 'fas fa-edit' , TauxExecPhys::class);
        yield MenuItem::linkToCrud('Observations' , 'fas fa-eye' , Observation::class);
        yield MenuItem::linkToCrud('Images' , 'fas fa-file-image' , Images::class);

        yield MenuItem::section('Planning');
        yield MenuItem::linkToCrud('Planning' , 'fas fa-calendar-alt' , Planning::class);
        yield MenuItem::linkToCrud('Tâches' , 'fas fa-tasks' , Tache::class);

        yield MenuItem::section('Approvisionnement');
        yield MenuItem::linkToCrud('Approvisionnement' , 'fas fa-briefcase' , Approvisionnement::class);
        yield MenuItem::linkToCrud('Element' , 'fas fa-wrench' , Element::class);
    }
}
