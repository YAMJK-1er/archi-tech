<?php

namespace App\Controller;

use App\Entity\Approvisionnement;
use App\Entity\Contributeur;
use App\Entity\Depense;
use App\Entity\Element;
use App\Entity\Images;
use App\Entity\Observation;
use App\Entity\Plan;
use App\Entity\Planning;
use App\Entity\Projet;
use App\Entity\Tache;
use App\Entity\TauxExecFin;
use App\Entity\TauxExecPhys;
use App\Entity\User;
use App\Form\DepensesFormType;
use App\Form\EditElementFormType;
use App\Form\EditTacheFormType;
use App\Form\ElementFormType;
use App\Form\ImageFormType;
use App\Form\ImportProjetFormType;
use App\Form\ObservationFormType;
use App\Form\PlanFormType;
use App\Form\ProjetFormType;
use App\Form\ShareProjectFormType;
use App\Form\TacheFormType;
use App\Form\TauxExecFinFormType;
use App\Form\TauxExecPhysFormType;
use App\Form\TermineProjetFormType;
use App\Form\UserFormType;
use App\Repository\ApprovisionnementRepository;
use App\Repository\ContributeurRepository;
use App\Repository\ElementRepository;
use App\Repository\ImagesRepository;
use App\Repository\ObservationRepository;
use App\Repository\PlanningRepository;
use App\Repository\PlanRepository;
use App\Repository\ProjetRepository;
use App\Repository\TacheRepository;
use App\Repository\TauxExecFinRepository;
use App\Repository\TauxExecPhysRepository;
use App\Repository\UserRepository;
use App\Security\LoginFormAuthenticator;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class ProjetsController extends AbstractController
{
    private $projetRepository;
    private $userRepository;
    private $entityManager;
    private $planningRepository;
    private $tacheRepository;
    private $elementRepository;
    private $approvRepository;
    private $tauxexecfinRepository;
    private $tauxexecphysRepository;
    private $observationRepository;
    private $imageRepository;
    private $contriRepository;
    private $planRepository;

    public function __construct(ProjetRepository $projetRepository , UserRepository $userRepository , EntityManagerInterface $entityManager , PlanningRepository $planningRepository , TacheRepository $tacheRepository , ApprovisionnementRepository $approvRepository , ElementRepository $elementRepository , TauxExecFinRepository $tauxexecfinRepository , TauxExecPhysRepository $tauxexecphysRepository , ObservationRepository $observationRepository , ImagesRepository $imageRepository , ContributeurRepository $contriRepository, PlanRepository $planRepository)
    {
        $this->projetRepository = $projetRepository;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->planningRepository = $planningRepository;
        $this->tacheRepository = $tacheRepository;
        $this->elementRepository = $elementRepository;
        $this->approvRepository = $approvRepository;
        $this->tauxexecfinRepository = $tauxexecfinRepository;
        $this->tauxexecphysRepository = $tauxexecphysRepository;
        $this->observationRepository = $observationRepository;
        $this->imageRepository = $imageRepository;
        $this->contriRepository = $contriRepository;
        $this->planRepository = $planRepository;
    }

    #[Route('/projets', name: 'app_projets')]
    public function index(): Response
    {
        $admin = false;
        $user = $this->getUser() ;

        $roles = $user->getRoles();

        foreach ($roles as $role) {
            if ($role == "ROLE_ADMIN"){
                $admin = true;
            }
        }
        
        $projets = $this->projetRepository->findBy(['user' => $user]) ;
        $importProjets = array();

        $contri = $this->contriRepository->findBy(['user_id' => $user->getId()]);

        
        if ($contri)
        {
            foreach($contri as $contriline)
            {
                $import = $this->projetRepository->findOneBy(['id' => $contriline->getProjetId()]);
                array_push($importProjets , $import);
            }
        }

        return $this->render('HTML/list_projets.html.twig', [
            'user' => $user,
            'projets' => $projets,
            'imports' => $importProjets,
            'admin' => $admin,
        ]);
    }

    #[Route('/', name: 'app')]
    public function index2(): Response
    {
        return $this->redirect('/login');
    }

    #[Route('/projets/{id}/details', name: 'show_project')]
    public function show($id): Response
    {
        $user = $this->getUser();

        $projet = $this->projetRepository->find($id);

        $planning = $this->planningRepository->findOneBy(['projet' => $projet]);

        $approv = $this->approvRepository->findOneBy(['projet' => $projet]);

        $execfin = $this->tauxexecfinRepository->findOneBy(['projet' => $projet]);

        $execphys = $this->tauxexecphysRepository->findOneBy(['projet' => $projet]);

        return $this->render('HTML/details_projet.html.twig', [
            'projet' => $projet,
            'user' => $user,
            'planning' => $planning,
            'approv' => $approv,
            'execfin' => $execfin,
            'execphys' => $execphys,
        ]);
    }

    #[Route('/generatepdf/projets/{id}', name: 'generatePdf')]
    public function generatePdf($id): Response
    {
        $user = $this->getUser();
        $projet = $this->projetRepository->find($id);
        $tauxexecfin = $this->tauxexecfinRepository->findOneBy(['projet' => $projet]);
        $tauxexecphys = $this->tauxexecphysRepository->findOneBy(['projet' => $projet]);

        $planning = $this->planningRepository->findOneBy(['projet' => $projet]);
        $taches = $this->tacheRepository->findBy(['planning' => $planning] , ['debut_prev' => 'asc']);

        $approv = $this->approvRepository->findOneBy(['projet' => $projet]);
        $elments = $this->elementRepository->findBy(['approvisionnement' => $approv]);

        $observations = $this->observationRepository->findBy(['projet' => $projet]);

        $images = $this->imageRepository->findBy(['projet' => $projet] , ['projet' => 'ASC'] , 8);

        $pdf = new Dompdf();

        $html =  $this->render('projets/showpdf.html.twig', [
            'projet' => $projet,
            'approv' => $approv,
            'elements' => $elments,
            'planning' => $planning,
            'taches' => $taches,
            'execfin' => $tauxexecfin,
            'execphys' => $tauxexecphys,
            'observations' => $observations,
            'images' => $images,
            'user' => $user,
        ]);

        $pdf->setPaper('A3' , 'landscape') ;

        $pdf->loadHtml($html);

        $pdf->render();

        $nomPdf = $projet->getNom() . '_' . date('dmY');
        $pdf->stream($nomPdf);

        return $this->redirect('/projets/'.$id.'/details');

    }

    #[Route('/termine/projets/{id}', name:'terminate_project')]
    public function Termine(Request $request , $id): Response
    {
        $user = $this->getUser();

        $projet = $this->projetRepository->find($id);
        $projet->setFin(new DateTimeImmutable());

        $planning = $this->planningRepository->findOneBy(['projet' => $projet]);

        $approv = $this->approvRepository->findOneBy(['projet' => $projet]);

        $execfin = $this->tauxexecfinRepository->findOneBy(['projet' => $projet]);

        $execphys = $this->tauxexecphysRepository->findOneBy(['projet' => $projet]);
        
        $form = $this->createForm(TermineProjetFormType::class , $projet) ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $projet->setFin($form->get('fin')->getData());

            $projet->setEstTermine(true);

            $this->entityManager->flush();

            $chemin = '/projets/' .$id . '/details' ;

            return $this->redirect($chemin);
        }

        return $this->render('HTML/termine_projet.html.twig' , [
            'user' => $user,
            'projet' => $projet,
            'planning' => $planning,
            'approv' => $approv,
            'execfin' => $execfin,
            'execphys' => $execphys,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/projet/create', name: 'create_project')]
    public function create(Request $request): Response
    {
        $projet = new Projet();

        $user = $this->getUser();

        $form = $this->createForm(ProjetFormType::class , $projet);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $newProjet = $form->getData() ;
            $newProjet->setUser($user);

            $planning = new Planning();
            $planning->setProjet($newProjet);

            $approv = new Approvisionnement();
            $approv->setProjet($projet);

            $tauxexecfin = new TauxExecFin();
            $tauxexecfin->setProjet($newProjet);
            $tauxexecfin->setBudget($form->get('budget')->getData());
            $tauxexecfin->setDepenses(0);
            $tauxexecfin->setTaux(0);

            $tauxexecphys = new TauxExecPhys();
            $tauxexecphys->setProjet($newProjet);
            $tauxexecphys->setDelai($form->get('delai')->getData() * 30);
            $tauxexecphys->setDuree(0);
            $tauxexecphys->setTaux(0);

            $comb = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
            $shfl = str_shuffle($comb);
            $pwd = substr($shfl,0,8);
            $code = $pwd . '' . substr(str_shuffle($form->get('nom')->getData()) , 0 , 4);

            $newProjet->setCode($code);

            $this->entityManager->persist($newProjet);
            $this->entityManager->persist($planning);
            $this->entityManager->persist($approv);
            $this->entityManager->persist($tauxexecfin);
            $this->entityManager->persist($tauxexecphys);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_projets');
        }

        return $this->render('HTML/create_projet.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/projets/{id}/addobservation', name: 'addObservation')]
    public function addObservation(Request $request , $id): Response
    {
        $user = $this->getUser();
        $user = $this->userRepository->find($user->getId());
        $projet = $this->projetRepository->find($id);
        $planning = $this->planningRepository->findOneBy(['projet' => $projet]);
        $approv = $this->approvRepository->findOneBy(['projet' => $projet]);
        $execfin = $this->tauxexecfinRepository->findOneBy(['projet' => $projet]);
        $execphys = $this->tauxexecphysRepository->findOneBy(['projet' => $projet]);

        $observation = new Observation();
        $form = $this->createForm(ObservationFormType::class , $observation) ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $observation->setProjet($projet);
            $observation->setMessage($form->get('message')->getData());
            $observation->setAuteur($user->getPrenom() . ' ' . $user->getNom());
            $observation->setPoste($user->getPoste());
            $observation->setDate($form->get('date')->getData());
            $observation->setUserId($user->getId());
    
            $this->entityManager->persist($observation);
            $this->entityManager->flush();

            $chemin = '/projets/'.$id.'/details/observations' ;

            return $this->redirect($chemin);
        }

        return $this->render('observations/add.html.twig' , [
            'form' => $form->createView(),
            'user' => $user,
            'projet' => $projet,
            'planning' => $planning,
            'approv' => $approv,
            'execfin' => $execfin,
            'execphys' => $execphys,
        ]);
    }

    #[Route('/projets/{id}/addimage', name: 'addImage')]
    public function addImages(Request $request , $id): Response
    {
        $user = $this->getUser();
        $projet = $this->projetRepository->find($id);
        $planning = $this->planningRepository->findOneBy(['projet' => $projet]);
        $approv = $this->approvRepository->findOneBy(['projet' => $projet]);
        $execfin = $this->tauxexecfinRepository->findOneBy(['projet' => $projet]);
        $execphys = $this->tauxexecphysRepository->findOneBy(['projet' => $projet]);

        $image = new Images();
        $form = $this->createForm(ImageFormType::class , $image) ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $image->setProjet($projet);
            $image->setDate($form->get('date')->getData());
            
            $path = $form->get('path')->getData();

            if ($path) {
                $filename = uniqid() . '.' . $path->guessExtension();

                try {
                    $path->move($this->getParameter('kernel.project_dir'). '/public/uploads' , $filename);
                } catch (FileException $e) {
                    return new Response($e->getMessage());
                }

                $image->setPath('/uploads/' . $filename);
            }
    
            $this->entityManager->persist($image);
            $this->entityManager->flush();

            $chemin = '/projets/'. $id . '/details/images' ;

            return $this->redirect($chemin);
        }

        return $this->render('images/add.html.twig' , [
            'form' => $form->createView(),
            'user' => $user,
            'projet' => $projet,
            'planning' => $planning,
            'approv' => $approv,
            'execfin' => $execfin,
            'execphys' => $execphys,
        ]);
    }

    #[Route('/projets/{idp}/approv/{ida}/addElement', name: 'addElement')]
    public function addElement(Request $request , $idp , $ida): Response
    {
        $element = new Element();
        $user = $this->getUser();
        $projet = $this->projetRepository->find($idp);
        $planning = $this->planningRepository->findOneBy(['projet' => $projet]);
        $approv = $this->approvRepository->find($ida);
        $execfin = $this->tauxexecfinRepository->findOneBy(['projet' => $projet]);
        $execphys = $this->tauxexecphysRepository->findOneBy(['projet' => $projet]);

        $form = $this->createForm(ElementFormType::class , $element);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $newElement = $form->getData() ;

            $newElement->setApprovisionnement($approv);
            $newElement->setQuantiteGlobale($form->get('stock_restant')->getData());

            $this->entityManager->persist($newElement);
            $this->entityManager->flush();

            $chemin = '/projets/'. $idp . '/details/approvisionnement/' . $ida ;

            return $this->redirect($chemin);
        }

        return $this->render('elements/create.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'projet' => $projet,
            'planning' => $planning,
            'execfin' => $execfin,
            'execphys' => $execphys,
            'approv' => $approv,
        ]);
    }

    #[Route('/projets/{idp}/approv/{ida}/element/{ide}/edit', name: 'editElement')]
    public function editElement(Request $request , $idp , $ida , $ide): Response
    {
        $element = $this->elementRepository->find($ide);
        $user = $this->getUser();
        $projet = $this->projetRepository->find($idp);
        $planning = $this->planningRepository->findOneBy(['projet' => $projet]);
        $approv = $this->approvRepository->find($ida);
        $execfin = $this->tauxexecfinRepository->findOneBy(['projet' => $projet]);
        $execphys = $this->tauxexecphysRepository->findOneBy(['projet' => $projet]);

        $form = $this->createForm(EditElementFormType::class , $element);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $new_stock = $form->get('stock_restant')->getData();

            $element->setStockRestant($new_stock);

            if ($element->getStockRestant() >= $element->getQuantiteGlobale())
            {
                $element->setQuantiteGlobale($element->getStockRestant());
            }

            $this->entityManager->flush();

            $chemin = '/projets/'. $idp . '/details/approvisionnement/' . $ida ;
            return $this->redirect($chemin);
        }

        return $this->render('elements/edit.html.twig', [
            'form' => $form->createView(),
            'element' => $element,
            'user' => $user, 
            'projet' => $projet,
            'planning' => $planning,
            'execfin' => $execfin,
            'execphys' => $execphys,
            'approv' => $approv,
        ]);
    }

    #[Route('/projets/{idp}/approv/{ida}/element/{ide}/delete', name: 'DeleteElement')]
    public function DeleteElement(Request $request , $idp , $ida , $ide): Response
    {
        $element = $this->elementRepository->find($ide);
        $user = $this->getUser();
        $projet = $this->projetRepository->find($idp);
        $approv = $this->approvRepository->find($ida);

        $this->entityManager->remove($element) ;

        $this->entityManager->flush();

        $chemin = '/projets/'. $idp . '/details/approvisionnement/' . $ida ;

        return $this->redirect($chemin);
    }

    #[Route('/projets/{idp}/observations/{ido}/delete', name: 'DeleteObservation')]
    public function DeleteObserv(Request $request , $idp , $ido): Response
    {
        $user = $this->getUser();
        $projet = $this->projetRepository->find($idp);
        $observ = $this->observationRepository->find($ido);

        $this->entityManager->remove($observ);

        $this->entityManager->flush();

        $chemin = '/projets/'. $idp . '/details/observations';
        return $this->redirect($chemin);
    }

    #[Route('/projets/{idp}/images/{ida}/delete', name: 'DeleteImages')]
    public function DeleteImages(Request $request , $idp , $ida): Response
    {
        $user = $this->getUser();
        $projet = $this->projetRepository->find($idp);
        $image = $this->imageRepository->find($ida);

        $this->entityManager->remove($image);

        $this->entityManager->flush();

        $chemin = '/projets/'. $idp . '/details/images';
        return $this->redirect($chemin);
    }

    #[Route('/projets/{idp}/planning/{ida}/addTache', name: 'addtache')]
    public function addTache(Request $request , $idp , $ida): Response
    {
        $tache = new Tache();
        $user = $this->getUser();
        $projet = $this->projetRepository->find($idp);
        $planning = $this->planningRepository->find($ida);
        $approv = $this->approvRepository->findOneBy(['projet' => $projet]);
        $execfin = $this->tauxexecfinRepository->findOneBy(['projet' => $projet]);
        $execphys = $this->tauxexecphysRepository->findOneBy(['projet' => $projet]);

        $form = $this->createForm(TacheFormType::class , $tache);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $newTache = $form->getData() ;

            $newTache->setPlanning($planning);

            $this->entityManager->persist($newTache);
            $this->entityManager->flush();

            $chemin = '/projets/'. $idp . '/details/planning/' . $ida ;
            return $this->redirect($chemin);
        }

        return $this->render('taches/create.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'projet' => $projet,
            'planning' => $planning,
            'approv' => $approv,
            'execfin' => $execfin,
            'execphys' => $execphys,
        ]);
    }

    #[Route('/projets/{idp}/planning/{ida}/tache/{idt}/edit', name: 'editTache')]
    public function editTache(Request $request , $idp , $ida , $idt): Response
    {
        $tache = $this->tacheRepository->find($idt);
        $tache->setDebutReel(new DateTimeImmutable());
        $tache->setDateFin(new DateTimeImmutable());

        $user = $this->getUser();
        $projet = $this->projetRepository->find($idp);
        $planning = $this->planningRepository->find($ida);
        $approv = $this->approvRepository->findOneBy(['projet' => $projet]);
        $execfin = $this->tauxexecfinRepository->findOneBy(['projet' => $projet]);
        $execphys = $this->tauxexecphysRepository->findOneBy(['projet' => $projet]);

        $form = $this->createForm(EditTacheFormType::class , $tache);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $tache->setDebutReel($form->get('debut_reel')->getData());
            $tache->setDateFin($form->get('date_fin')->getData());

            $debut_reel = $form->get('debut_reel')->getData();
            $fin_reel = $form->get('date_fin')->getData();

            $delai_reel = date_diff($fin_reel, $debut_reel);
            $tache->setDelaiReel($delai_reel->days + 1);
            $tache->setCoutReel($form->get('cout_reel')->getData());
            $tache->setEstRealise($form->get('est_realise')->getData());

            $this->entityManager->flush();

            $chemin = '/projets/'. $idp . '/details/planning/' . $ida ;
            return $this->redirect($chemin);
        }

        return $this->render('taches/edit.html.twig', [
            'form' => $form->createView(),
            'tache' => $tache,
            'user' => $user,
            'projet' => $projet,
            'planning' => $planning,
            'approv' => $approv,
            'execfin' => $execfin,
            'execphys' => $execphys,
        ]);
    }

    #[Route('/projets/{idp}/planning/{ida}/tache/{idt}/delete', name: 'DeleteTache')]
    public function DeleteTache(Request $request , $idp , $ida , $idt): Response
    {
        $tache = $this->tacheRepository->find($idt);
        $user = $this->getUser();
        $projet = $this->projetRepository->find($idp);
        $approv = $this->approvRepository->find($ida);

        $this->entityManager->remove($tache) ;

        $this->entityManager->flush();

        $chemin = '/projets/'. $idp . '/details/planning/' . $ida ;

        return $this->redirect($chemin);
    
    }

    #[Route('/projets/{idp}/tauxexecfin/{idtaux}/edit', name: 'editTauxExecFin')]
    public function editTauxExecFin(Request $request , $idp , $idtaux): Response
    {
        $user = $this->getUser();
        $projet = $this->projetRepository->find($idp);
        $planning = $this->planningRepository->findOneBy(['projet' => $projet]);
        $approv = $this->approvRepository->findOneBy(['projet' => $projet]);
        $execphys = $this->tauxexecfinRepository->findOneBy(['projet' => $projet]);
        $tauxexecfin = $this->tauxexecfinRepository->find($idtaux);

        $form = $this->createForm(TauxExecFinFormType::class , $tauxexecfin);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $tauxexecfin->setDepenses($form->get('depenses')->getData());
            $taux = $tauxexecfin->getDepenses() / $tauxexecfin->getBudget();
            $taux = round($taux , 4);
            $tauxexecfin->setTaux($taux);

            $this->entityManager->flush();

            $chemin = '/projets/'. $idp . '/details/execution_financiere/' . $idtaux;
            return $this->redirect($chemin);
        }

        return $this->render('taux/editTauxFin.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'projet' => $projet,
            'planning' => $planning,
            'approv' => $approv,
            'execfin' => $tauxexecfin,
            'execphys' => $execphys,
        ]);
    }

    #[Route('/projets/{idp}/tauxexecphys/{idtaux}/edit', name: 'editTauxExecPhys')]
    public function editTauxExecPhys(Request $request , $idp , $idtaux): Response
    {
        $user = $this->getUser();
        $projet = $this->projetRepository->find($idp);
        $planning = $this->planningRepository->findOneBy(['projet' => $projet]);
        $approv = $this->approvRepository->findOneBy(['projet' => $projet]);
        $execfin = $this->tauxexecfinRepository->findOneBy(['projet' => $projet]);
        $tauxexecphys = $this->tauxexecphysRepository->find($idtaux);

        $form = $this->createForm(TauxExecPhysFormType::class , $tauxexecphys);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $tauxexecphys->setDuree($form->get('duree')->getData());
            $taux = $tauxexecphys->getDuree() / $tauxexecphys->getDelai();
            $taux = round($taux , 4);
            $tauxexecphys->setTaux($taux);

            $this->entityManager->flush();

            $chemin = '/projets/'. $idp . '/details/execution_physique/' . $idtaux;
            return $this->redirect($chemin);
        }

        return $this->render('taux/editTauxPhys.html.twig', [
            'form' => $form->createView(),
            'user' => $user ,
            'projet' => $projet,
            'planning' => $planning,
            'approv' => $approv,
            'execfin' => $execfin,
            'execphys' => $tauxexecphys,
        ]);
    }

    #[Route('/projet/import', name: 'import_project')]
    public function import(Request $request): Response
    {
        $projet = new Projet();
        $user = $this->getUser();
        $user = $this->userRepository->find($user->getId());

        $form = $this->createForm(ImportProjetFormType::class , $projet);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
           $nom = $form->get('nom')->getData();
           $code = $form->get('code')->getData();
           
           $projet = $this->projetRepository->findOneBy(['nom' => $nom , 'code' => $code]);

            if ($projet)
            {
                $contri = new Contributeur();

                $contri->setUserId($user->getId()) ;
                $contri->setProjetId($projet->getId());
                
                $this->entityManager->persist($contri);
                $this->entityManager->flush();

                return $this->redirectToRoute('app_projets');
            }
            
            else
            {
                return $this->render('projets/import.html.twig', [
                    'form' => $form->createView(),
                    'text' => 'Projet non trouvé, veuillez vérifier que le nom ou le code du projet soit correct',
                    'user' => $user,
                ]);
            }
        }

        return $this->render('HTML/import_projet.html.twig', [
            'form' => $form->createView(),
            'text' => '',
            'user' => $user,
        ]);
    }

    #[Route('/projets/{id}/details/planning/{idp}', name: 'show_planning')]
    public function showplanning($id , $idp)
    {
        $user = $this->getUser();

        $projet = $this->projetRepository->find($id);

        $planning = $this->planningRepository->find($idp);
        
        $taches = $this->tacheRepository->findBy(['planning' => $planning]);

        $approv = $this->approvRepository->findOneBy(['projet' => $projet]);

        $execfin = $this->tauxexecfinRepository->findOneBy(['projet' => $projet]);

        $execphys = $this->tauxexecphysRepository->findOneBy(['projet' => $projet]);

        return $this->render('HTML/planning_gen.html.twig', [
            'projet' => $projet,
            'user' => $user,
            'planning' => $planning,
            'taches' => $taches,
            'approv' => $approv,
            'execfin' => $execfin,
            'execphys' => $execphys,
        ]);
    }

    #[Route('/projets/{id}/details/planning/{idp}/taches_a_realiser', name: 'show_taches_a_realiser')]
    public function showtachesarealiser($id , $idp)
    {
        $user = $this->getUser();

        $projet = $this->projetRepository->find($id);

        $planning = $this->planningRepository->find($idp);
        
        $taches = $this->tacheRepository->findBy(['planning' => $planning , 'est_realise' => null]);

        $approv = $this->approvRepository->findOneBy(['projet' => $projet]);

        $execfin = $this->tauxexecfinRepository->findOneBy(['projet' => $projet]);

        $execphys = $this->tauxexecphysRepository->findOneBy(['projet' => $projet]);

        return $this->render('HTML/planning_taches_a_realiser.html.twig', [
            'projet' => $projet,
            'user' => $user,
            'planning' => $planning,
            'taches' => $taches,
            'approv' => $approv,
            'execfin' => $execfin,
            'execphys' => $execphys,
        ]);
    }

    #[Route('/projets/{id}/details/planning/{idp}/taches_realisees', name: 'show_taches_realisees')]
    public function showtachesrealisees($id , $idp)
    {
        $user = $this->getUser();

        $projet = $this->projetRepository->find($id);

        $planning = $this->planningRepository->find($idp);
        
        $taches = $this->tacheRepository->findBy(['planning' => $planning , 'est_realise' => true]);

        $approv = $this->approvRepository->findOneBy(['projet' => $projet]);

        $execfin = $this->tauxexecfinRepository->findOneBy(['projet' => $projet]);

        $execphys = $this->tauxexecphysRepository->findOneBy(['projet' => $projet]);

        return $this->render('HTML/planning_taches_realisees.html.twig', [
            'projet' => $projet,
            'user' => $user,
            'planning' => $planning,
            'taches' => $taches,
            'approv' => $approv,
            'execfin' => $execfin,
            'execphys' => $execphys,
        ]);
    }

    #[Route('/projets/{id}/details/approvisionnement/{ida}', name: 'show_approv')]
    public function showapprov($id , $ida)
    {
        $user = $this->getUser();

        $projet = $this->projetRepository->find($id);

        $planning = $this->planningRepository->findOneBy(['projet' => $projet]);
        
        $approv = $this->approvRepository->find($ida);

        $elements = $this->elementRepository->findBy(['approvisionnement' => $approv]);

        $execfin = $this->tauxexecfinRepository->findOneBy(['projet' => $projet]);

        $execphys = $this->tauxexecphysRepository->findOneBy(['projet' => $projet]);

        return $this->render('HTML/approvisionnement.html.twig', [
            'projet' => $projet,
            'user' => $user,
            'planning' => $planning,
            'approv' => $approv,
            'elements' => $elements,
            'execfin' => $execfin,
            'execphys' => $execphys,
        ]);
    }

    #[Route('/projets/{idp}/details/execution_financiere/{idf}')]
    public function tauxexecfin($idp , $idf)
    {
        $user = $this->getUser();

        $projet = $this->projetRepository->find($idp);

        $planning = $this->planningRepository->findOneBy(['projet' => $projet]);

        $approv = $this->approvRepository->findOneBy(['projet' => $projet]);

        $execfin = $this->tauxexecfinRepository->find($idf);

        $execphys = $this->tauxexecphysRepository->findOneBy(['projet' => $projet]);

        $depenses = $projet->getDepenses();

        $total = 0;

        foreach($depenses as $depense){
            $total += $depense->getTotal();
        }

        $execfin->setDepenses($total);

        $taux = $execfin->getDepenses() / $execfin->getBudget();
        $taux = round($taux, 4);
        
        $execfin->setTaux($taux);

        $this->entityManager->flush();

        return $this->render('HTML/execution_financiere.html.twig',[
            'user' => $user,
            'projet' => $projet,
            'planning' => $planning,
            'approv' => $approv,
            'execfin' => $execfin,
            'execphys' => $execphys,
        ]);
    }

    #[Route('/projets/{idp}/details/execution_physique/{idph}')]
    public function tauxexecphys($idp , $idph)
    {
        $user = $this->getUser();

        $projet = $this->projetRepository->find($idp);

        $planning = $this->planningRepository->findOneBy(['projet' => $projet]);

        $approv = $this->approvRepository->findOneBy(['projet' => $projet]);

        $execfin = $this->tauxexecfinRepository->findOneBy(['projet' => $projet]);

        $execphys = $this->tauxexecphysRepository->find($idph);

        if (!$projet->getEstTermine()){
            $today = date_create();

            $debut = $projet->getDemarrage();

            $duree = date_diff($debut, $today);

            $execphys->setDuree($duree->days + 1);

            $taux = $execphys->getDuree() / $execphys->getDelai();
            $taux = round($taux , 4);
            $execphys->setTaux($taux);

            $this->entityManager->flush();
        }

        else{
            $debut = $projet->getDemarrage();

            $fin = $projet->getFin();

            $duree = date_diff($debut, $fin);

            $execphys->setDuree($duree->days + 1);

            $taux = $execphys->getDuree() / $execphys->getDelai();
            $taux = round($taux , 4);
            $execphys->setTaux($taux);

            $this->entityManager->flush();
        }

        return $this->render('HTML/execution_physique.html.twig',[
            'user' => $user,
            'projet' => $projet,
            'planning' => $planning,
            'approv' => $approv,
            'execfin' => $execfin,
            'execphys' => $execphys,
        ]);
    }

    #[Route('projets/{idp}/details/observations' , name:'show_observations')]
    public  function show_observations($idp)
    {
        $user = $this->getUser();

        $projet = $this->projetRepository->find($idp);

        $planning = $this->planningRepository->findOneBy(['projet' => $projet]);

        $approv = $this->approvRepository->findOneBy(['projet' => $projet]);

        $execfin = $this->tauxexecfinRepository->findOneBy(['projet' => $projet]);

        $execphys = $this->tauxexecphysRepository->findOneBy(['projet' => $projet]);

        $observations = $this->observationRepository->findBy(['projet' => $projet]);

        return $this->render('HTML/observations.html.twig' , [
            'user' => $user,
            'projet' => $projet,
            'planning' => $planning,
            'approv' => $approv,
            'execfin' => $execfin,
            'execphys' => $execphys,
            'observations' => $observations,
        ]);
    }

    #[Route('projets/{idp}/details/images' , name:'show_images')]
    public  function show_images($idp)
    {
        $user = $this->getUser();

        $projet = $this->projetRepository->find($idp);

        $planning = $this->planningRepository->findOneBy(['projet' => $projet]);

        $approv = $this->approvRepository->findOneBy(['projet' => $projet]);

        $execfin = $this->tauxexecfinRepository->findOneBy(['projet' => $projet]);

        $execphys = $this->tauxexecphysRepository->findOneBy(['projet' => $projet]);

        $images = $this->imageRepository->findBy(['projet' => $projet] , ['date' => 'DESC'] , 8);

        return $this->render('HTML/images.html.twig' , [
            'user' => $user,
            'projet' => $projet,
            'planning' => $planning,
            'approv' => $approv,
            'execfin' => $execfin,
            'execphys' => $execphys,
            'images' => $images,
        ]);
    }

    #[Route('/projet/{idp}/share', name:'Projet-Partage')]
    public function shareProject(Request $request, $idp, MailerInterface $mailer){
        $user = $this->getUser();

        $project = $this->projetRepository->find($idp);

        $form = $this->createForm(ShareProjectFormType::class, $project);
        $form->handleRequest($request);

        $message = null;

        if ($form->isSubmitted() && $form->isValid()){
            $dest = $this->userRepository->findOneBy(['email' => $form->get('email')->getData()]);

            if ($dest){
                $mail = new TemplatedEmail();

                $mail->from('yamjk1er@gmail.com');
                $mail->to($form->get('email')->getData());
                $mail->subject('Nouveau projet Africa-Etudes');

                $mail->htmlTemplate('email/share.html.twig');
                $mail->context([
                    'user' => $user,
                    'dest' => $dest,
                    'nom' => $project->getNom(),
                    'code' => $project->getCode(),
                ]);

                $mailer->send($mail);

                return $this->redirectToRoute('show_project', ['id' => $idp]);
            }

            else{
                $message = 'Aucun compte Africa-Etudes n\'est lié à cet email';
            }
        }

        return $this->render('HTML/share_project.html.twig', [
            'form' => $form->createView(),
            'message' => $message,
            'user' => $user,
            'projet' => $project,
        ]);
    }

    #[Route('projets/{idp}/details/depenses', name:'Projet-Depenses')]
    public function Depenses($idp){
        $user = $this->getUser();

        $projet = $this->projetRepository->find($idp);

        $depenses = $projet->getDepenses();

        $planning = $this->planningRepository->findOneBy(['projet' => $projet]);

        $approv = $this->approvRepository->findOneBy(['projet' => $projet]);

        $execfin = $this->tauxexecfinRepository->findOneBy(['projet' => $projet]);

        $execphys = $this->tauxexecphysRepository->findOneBy(['projet' => $projet]);

        return $this->render('HTML/depenses.html.twig', [
            'projet' => $projet,
            'user' => $user,
            'planning' => $planning,
            'approv' => $approv,
            'execfin' => $execfin,
            'execphys' => $execphys,
            'depenses' => $depenses,
        ]);
    }

    #[Route('projets/{idp}/details/depenses/ajouter', name:'Projet-Depenses-Ajouter')]
    public function AddDepenses(Request $request, $idp){
        $user = $this->getUser();

        $projet = $this->projetRepository->find($idp);

        $planning = $this->planningRepository->findOneBy(['projet' => $projet]);

        $approv = $this->approvRepository->findOneBy(['projet' => $projet]);

        $execfin = $this->tauxexecfinRepository->findOneBy(['projet' => $projet]);

        $execphys = $this->tauxexecphysRepository->findOneBy(['projet' => $projet]);

        $depense = new Depense();

        $form = $this->createForm(DepensesFormType::class, $depense);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $depense->setIntitule($form->get('intitule')->getData());

            $depense->setDate($form->get('date')->getData());

            $depense->setUnite($form->get('unite')->getData());

            if ($form->get('quantite')->getData()){
                $depense->setQuantite($form->get('quantite')->getData());
            }
            
            if ($depense->getQuantite()){
                $depense->setTotal($depense->getUnite() * $depense->getQuantite());
            }

            else{
                $depense->setTotal($depense->getUnite());
            }

            $depense->setProjet($projet);

            $this->entityManager->persist($depense);
            $this->entityManager->flush();

            return $this->redirectToRoute('Projet-Depenses', ['idp' => $projet->getId()]);
        }

        return $this->render('depenses/add.html.twig', [
            'projet' => $projet,
            'user' => $user,
            'planning' => $planning,
            'approv' => $approv,
            'execfin' => $execfin,
            'execphys' => $execphys,
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('projets/{idp}/details/plans' , name:'show_plans')]
    public  function show_plan($idp)
    {
        $user = $this->getUser();

        $projet = $this->projetRepository->find($idp);

        $planning = $this->planningRepository->findOneBy(['projet' => $projet]);

        $approv = $this->approvRepository->findOneBy(['projet' => $projet]);

        $execfin = $this->tauxexecfinRepository->findOneBy(['projet' => $projet]);

        $execphys = $this->tauxexecphysRepository->findOneBy(['projet' => $projet]);

        $plans = $projet->getPlans();

        return $this->render('HTML/plans.html.twig' , [
            'user' => $user,
            'projet' => $projet,
            'planning' => $planning,
            'approv' => $approv,
            'execfin' => $execfin,
            'execphys' => $execphys,
            'plans' => $plans,
        ]);
    }

    #[Route('/projets/{id}/addplan', name: 'addPlan')]
    public function addPlan(Request $request , $id): Response
    {
        $user = $this->getUser();
        $projet = $this->projetRepository->find($id);
        $planning = $this->planningRepository->findOneBy(['projet' => $projet]);
        $approv = $this->approvRepository->findOneBy(['projet' => $projet]);
        $execfin = $this->tauxexecfinRepository->findOneBy(['projet' => $projet]);
        $execphys = $this->tauxexecphysRepository->findOneBy(['projet' => $projet]);

        $plan = new Plan();
        $form = $this->createForm(PlanFormType::class , $plan) ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $plan->setProjet($projet);
            $plan->setNom($form->get('nom')->getData());
            
            $path = $form->get('path')->getData();

            if ($path) {
                $filename = uniqid() . '.' . $path->guessExtension();

                try {
                    $path->move($this->getParameter('kernel.project_dir'). '/public/plans' , $filename);
                } catch (FileException $e) {
                    return new Response($e->getMessage());
                }

                $plan->setPath('/plans/' . $filename);
            }
    
            $this->entityManager->persist($plan);
            $this->entityManager->flush();


            return $this->redirectToRoute('show_plans', ['idp' => $projet->getId()]);
        }

        return $this->render('plan/add.html.twig' , [
            'form' => $form->createView(),
            'user' => $user,
            'projet' => $projet,
            'planning' => $planning,
            'approv' => $approv,
            'execfin' => $execfin,
            'execphys' => $execphys,
        ]);
    }

    #[Route('/projets/{idp}/plans/{idpl}/delete', name: 'DeletePlans')]
    public function DeletePlan($idp , $idpl): Response
    {
        $projet = $this->projetRepository->find($idp);
        $plan = $this->planRepository->find($idpl);

        $projet->removePlan($plan);

        $this->entityManager->flush();

        $chemin = '/projets/'. $idp . '/details/plans';
        return $this->redirect($chemin);
    }
}
