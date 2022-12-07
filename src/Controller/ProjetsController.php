<?php

namespace App\Controller;

use App\Entity\Approvisionnement;
use App\Entity\Contributeur;
use App\Entity\Depense;
use App\Entity\Element;
use App\Entity\Images;
use App\Entity\Mouvement;
use App\Entity\Observation;
use App\Entity\Ouvrier;
use App\Entity\Plan;
use App\Entity\Planning;
use App\Entity\Presence;
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
use App\Form\MouvementFormType;
use App\Form\ObservationFormType;
use App\Form\OuvrierFormType;
use App\Form\PlanFormType;
use App\Form\PlanningFormType;
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
use App\Repository\MouvementRepository;
use App\Repository\ObservationRepository;
use App\Repository\PlanningRepository;
use App\Repository\PlanRepository;
use App\Repository\PresenceRepository;
use App\Repository\ProjetRepository;
use App\Repository\TacheRepository;
use App\Repository\TauxExecFinRepository;
use App\Repository\TauxExecPhysRepository;
use App\Repository\UserRepository;
use App\Security\LoginFormAuthenticator;
use DateTime;
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
    private $mouvementRepository;
    private $presenceRepository;

    public function __construct(ProjetRepository $projetRepository , UserRepository $userRepository , EntityManagerInterface $entityManager , PlanningRepository $planningRepository , TacheRepository $tacheRepository , ApprovisionnementRepository $approvRepository , ElementRepository $elementRepository , TauxExecFinRepository $tauxexecfinRepository , TauxExecPhysRepository $tauxexecphysRepository , ObservationRepository $observationRepository , ImagesRepository $imageRepository , ContributeurRepository $contriRepository, PlanRepository $planRepository, MouvementRepository $mouvementRepository, PresenceRepository $presenceRepository)
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
        $this->mouvementRepository = $mouvementRepository;
        $this->presenceRepository = $presenceRepository;
    }

    #[Route('/projets', name: 'app_projets')]
    public function index(): Response
    {
        $user = $this->getUser() ;

        $roles = $user->getRoles();

        foreach ($roles as $role){
            if ($role == 'ROLE_ADMIN'){
                return $this->redirectToRoute('Admin_Panel');
                break;
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

        if ($user->getEmail() != $projet->getUser()->getEmail()){
            return $this->redirectToRoute('app_projets');
        }

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

    #[Route('/termine/projets/{id}', name:'terminate_project')]
    public function Termine(Request $request , $id): Response
    {
        $user = $this->getUser();

        $projet = $this->projetRepository->find($id);
        $projet->setFin(new DateTimeImmutable());

        if ($user->getEmail() != $projet->getUser()->getEmail()){
            return $this->redirectToRoute('app_projets');
        }

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

        if ($user->getEmail() != $projet->getUser()->getEmail()){
            return $this->redirectToRoute('app_projets');
        }

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

        if ($user->getEmail() != $projet->getUser()->getEmail()){
            return $this->redirectToRoute('app_projets');
        }

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

    #[Route('/projets/{idp}/approv/addElement', name: 'addElement')]
    public function addElement(Request $request , $idp): Response
    {
        $element = new Element();
        $user = $this->getUser();
        $projet = $this->projetRepository->find($idp);

        if ($user->getEmail() != $projet->getUser()->getEmail()){
            return $this->redirectToRoute('app_projets');
        }

        $planning = $this->planningRepository->findOneBy(['projet' => $projet]);
        $approv = $projet->getApprovisionnement();
        $execfin = $this->tauxexecfinRepository->findOneBy(['projet' => $projet]);
        $execphys = $this->tauxexecphysRepository->findOneBy(['projet' => $projet]);

        $form = $this->createForm(ElementFormType::class , $element);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $newElement = $form->getData() ;

            $newElement->setApprovisionnement($approv);
            $newElement->setStockRestant(0);

            $this->entityManager->persist($newElement);
            $this->entityManager->flush();

            $chemin = '/projets/'. $idp . '/details/approvisionnement' ;

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

    #[Route('/projets/{idp}/approv/element/{ide}/edit', name: 'editElement')]
    public function editElement(Request $request , $idp , $ide): Response
    {
        $element = $this->elementRepository->find($ide);
        $user = $this->getUser();
        $projet = $this->projetRepository->find($idp);

        if ($user->getEmail() != $projet->getUser()->getEmail()){
            return $this->redirectToRoute('app_projets');
        }

        $planning = $this->planningRepository->findOneBy(['projet' => $projet]);
        $approv = $projet->getApprovisionnement();
        $execfin = $this->tauxexecfinRepository->findOneBy(['projet' => $projet]);
        $execphys = $this->tauxexecphysRepository->findOneBy(['projet' => $projet]);

        $form = $this->createForm(EditElementFormType::class , $element);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $new_stock = $form->get('stock_restant')->getData();

            $element->setStockRestant($new_stock);

            $this->entityManager->flush();

            $chemin = '/projets/'. $idp . '/details/approvisionnement' ;
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

    #[Route('/projets/{idp}/approv/element/{ide}/delete', name: 'DeleteElement')]
    public function DeleteElement(Request $request , $idp , $ide): Response
    {
        $element = $this->elementRepository->find($ide);
        $user = $this->getUser();
        $projet = $this->projetRepository->find($idp);

        if ($user->getEmail() != $projet->getUser()->getEmail()){
            return $this->redirectToRoute('app_projets');
        }

        $approv = $projet->getApprovisionnement();

        $this->entityManager->remove($element) ;

        $this->entityManager->flush();

        $chemin = '/projets/'. $idp . '/details/approvisionnement' ;

        return $this->redirect($chemin);
    }

    #[Route('/projets/{idp}/observations/{ido}/delete', name: 'DeleteObservation')]
    public function DeleteObserv(Request $request , $idp , $ido): Response
    {
        $user = $this->getUser();
        $projet = $this->projetRepository->find($idp);
        $observ = $this->observationRepository->find($ido);

        if ($user->getEmail() != $projet->getUser()->getEmail()){
            return $this->redirectToRoute('app_projets');
        }

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

        if ($user->getEmail() != $projet->getUser()->getEmail()){
            return $this->redirectToRoute('app_projets');
        }

        $this->entityManager->remove($image);

        $this->entityManager->flush();

        $chemin = '/projets/'. $idp . '/details/images';
        return $this->redirect($chemin);
    }

    #[Route('/projets/{idp}/planning/addTache', name: 'addtache')]
    public function addTache(Request $request , $idp): Response
    {
        $tache = new Tache();
        $user = $this->getUser();
        $projet = $this->projetRepository->find($idp);

        if ($user->getEmail() != $projet->getUser()->getEmail()){
            return $this->redirectToRoute('app_projets');
        }

        $planning = $projet->getPlanning();
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

            $chemin = '/projets/'. $idp . '/details/planning' ;
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

    #[Route('/projets/{idp}/planning/tache/{idt}/terminer', name: 'Terminer-Tache')]
    public function terminerTache($idp , $idt): Response
    {
        $tache = $this->tacheRepository->find($idt);

        $user = $this->getUser();
        $projet = $this->projetRepository->find($idp);

        if ($user->getEmail() != $projet->getUser()->getEmail()){
            return $this->redirectToRoute('app_projets');
        }
            $debut_reel = $tache->getDebutReel();

            if ($debut_reel){
                $tache->setDateFin(new DateTimeImmutable());
                $fin_reel = $tache->getDateFin();

                $delai_reel = date_diff($fin_reel, $debut_reel);
                $tache->setDelaiReel($delai_reel->days + 1);
                $tache->setEstRealise(true);

                $this->entityManager->flush();

                $chemin = '/projets/'. $idp . '/details/planning/taches_realisees' ;
                return $this->redirect($chemin);
            }

            else{
                return $this->redirectToRoute('show_taches_a_realiser', ['id' => $idp]);
            }    
    }

    #[Route('/projets/{idp}/planning/tache/{idt}/debuter', name: 'Debuter-Tache')]
    public function debuterTache($idp , $idt): Response
    {
        $tache = $this->tacheRepository->find($idt);

        $user = $this->getUser();
        $projet = $this->projetRepository->find($idp);

        if ($user->getEmail() != $projet->getUser()->getEmail()){
            return $this->redirectToRoute('app_projets');
        }

        $tache->setDebutReel(new DateTimeImmutable());
        
        $this->entityManager->flush();

        return $this->redirectToRoute('show_taches_a_realiser', ['id' => $idp]);
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

    #[Route('/projets/{id}/details/planning', name: 'show_planning')]
    public function showplanning(Request $request, $id)
    {
        $user = $this->getUser();

        $projet = $this->projetRepository->find($id);

        if ($user->getEmail() != $projet->getUser()->getEmail()){
            return $this->redirectToRoute('app_projets');
        }

        $planning = $projet->getPlanning();
        
        $taches = $this->tacheRepository->findBy(['planning' => $planning]);

        $approv = $this->approvRepository->findOneBy(['projet' => $projet]);

        $execfin = $this->tauxexecfinRepository->findOneBy(['projet' => $projet]);

        $execphys = $this->tauxexecphysRepository->findOneBy(['projet' => $projet]);


        $form = $this->createForm(PlanningFormType::class, $planning);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $path = $form->get('planning')->getData();

            if ($path) {
                $filename = uniqid() . '.csv'; //. $path->guessExtension();

                try {
                    $path->move($this->getParameter('kernel.project_dir'). '/public/plans' , $filename);
                } catch (FileException $e) {
                    return new Response($e->getMessage());
                }

                $planning->setPlanning($filename);

                $this->entityManager->flush();
                
                return $this->redirectToRoute('Generate_Planning', ['idp' => $id]);
            }
        }


        return $this->render('HTML/planning_gen.html.twig', [
            'projet' => $projet,
            'user' => $user,
            'planning' => $planning,
            'taches' => $taches,
            'approv' => $approv,
            'execfin' => $execfin,
            'execphys' => $execphys,

            'form' => $form->createView(),
        ]);
    }

    #[Route('/projets/{id}/details/planning/taches_a_realiser', name: 'show_taches_a_realiser')]
    public function showtachesarealiser($id)
    {
        $user = $this->getUser();

        $projet = $this->projetRepository->find($id);

        if ($user->getEmail() != $projet->getUser()->getEmail()){
            return $this->redirectToRoute('app_projets');
        }

        $planning = $projet->getPlanning();
        
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

    #[Route('/projets/{id}/details/planning/taches_realisees', name: 'show_taches_realisees')]
    public function showtachesrealisees($id)
    {
        $user = $this->getUser();

        $projet = $this->projetRepository->find($id);

        if ($user->getEmail() != $projet->getUser()->getEmail()){
            return $this->redirectToRoute('app_projets');
        }

        $planning = $projet->getPlanning();
        
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

    #[Route('/projets/{id}/details/approvisionnement', name: 'show_approv')]
    public function showapprov($id)
    {
        $user = $this->getUser();

        $projet = $this->projetRepository->find($id);

        if ($user->getEmail() != $projet->getUser()->getEmail()){
            return $this->redirectToRoute('app_projets');
        }

        $planning = $this->planningRepository->findOneBy(['projet' => $projet]);
        
        $approv = $projet->getApprovisionnement();

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

    #[Route('/projets/{idp}/details/execution_financiere')]
    public function tauxexecfin($idp)
    {
        $user = $this->getUser();

        $projet = $this->projetRepository->find($idp);

        if ($user->getEmail() != $projet->getUser()->getEmail()){
            return $this->redirectToRoute('app_projets');
        }

        $planning = $this->planningRepository->findOneBy(['projet' => $projet]);

        $approv = $this->approvRepository->findOneBy(['projet' => $projet]);

        $execfin = $projet->getTauxExecFin();

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

    #[Route('/projets/{idp}/details/execution_physique')]
    public function tauxexecphys($idp)
    {
        $user = $this->getUser();

        $projet = $this->projetRepository->find($idp);

        if ($user->getEmail() != $projet->getUser()->getEmail()){
            return $this->redirectToRoute('app_projets');
        }

        $planning = $this->planningRepository->findOneBy(['projet' => $projet]);

        $approv = $this->approvRepository->findOneBy(['projet' => $projet]);

        $execfin = $this->tauxexecfinRepository->findOneBy(['projet' => $projet]);

        $execphys = $projet->getTauxExecPhys();

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

        if ($user->getEmail() != $projet->getUser()->getEmail()){
            return $this->redirectToRoute('app_projets');
        }

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

        if ($user->getEmail() != $projet->getUser()->getEmail()){
            return $this->redirectToRoute('app_projets');
        }

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

        if ($user->getEmail() != $project->getUser()->getEmail()){
            return $this->redirectToRoute('app_projets');
        }

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

        if ($user->getEmail() != $projet->getUser()->getEmail()){
            return $this->redirectToRoute('app_projets');
        }

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

        if ($user->getEmail() != $projet->getUser()->getEmail()){
            return $this->redirectToRoute('app_projets');
        }

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

        if ($user->getEmail() != $projet->getUser()->getEmail()){
            return $this->redirectToRoute('app_projets');
        }

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

        if ($user->getEmail() != $projet->getUser()->getEmail()){
            return $this->redirectToRoute('app_projets');
        }

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
        $user = $this->getUser();
        $projet = $this->projetRepository->find($idp);

        if ($user->getEmail() != $projet->getUser()->getEmail()){
            return $this->redirectToRoute('app_projets');
        }

        $plan = $this->planRepository->find($idpl);

        $projet->removePlan($plan);

        $this->entityManager->flush();

        $chemin = '/projets/'. $idp . '/details/plans';
        return $this->redirect($chemin);
    }

    #[Route('/projets/{idp}/details/planning/generate', name:'Generate_Planning')]
    public function generatePlanning(Request $request, $idp)
    {
        $user = $this->getUser();
        $projet = $this->projetRepository->find($idp);

        if ($user->getEmail() != $projet->getUser()->getEmail()){
            return $this->redirectToRoute('app_projets');
        }

        $planning = $projet->getPlanning();

        $path = $this->getParameter('kernel.project_dir') .'/public/plans/planning.csv'; //. $planning->getPlanning();

        $file = fopen($path, 'r');

        if ($file !== FALSE){
            $i = 0;
            while($data = fgetcsv($file, 1000, ';')){
                if ($data[0] != "" && $data[1] != "" && $data[4] != ""){
                    $array[] = $data;
                }
            }

            foreach($array as $data){
                if ($i == 0){
                    $i++;
                    continue;
                }

                $tache = new Tache();

                $tache->setIntitule($data[1]);
                if (intval($data[4]) == 0){
                    continue;
                }
                else{
                    $tache->setDelai(intval($data[4]));
                }

                $this->entityManager->persist($tache);

                $planning->addTach($tache);

                $this->entityManager->flush();
            }

            fclose($file);

            return $this->redirectToRoute('show_planning', ['id' => $idp]);
        }

        else{
            return $this->redirectToRoute('show_planning', ['id' => $idp,]);
        }
    }

    #[Route('/adminpannel', name:'Admin_Panel')]
    public function adminPannel(){
        $user = $this->getUser();

        $users = $this->userRepository->findAll();

        array_shift($users);

        return $this->render('HTML/adminpannel.html.twig', [
            'users' => $users,
            'user' => $user,
        ]);
    }

    #[Route('/profil', name:'User-Profil')]
    public function profil(){
        $user = $this->getUser();

        return $this->render('HTML/profil.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/user/{id}/profil', name:'User-Profil-Admin')]
    public function userprofil($id){
        $user = $this->userRepository->find($id);

        return $this->render('HTML/user.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/projets/{id}/details/approvisionnement/element/{ide}/mouvement', name:'Mouvement')]
    public function mouvement($id, $ide){
        $user = $this->getUser();

        $projet = $this->projetRepository->find($id);

        if ($user->getEmail() != $projet->getUser()->getEmail()){
            return $this->redirectToRoute('app_projets');
        }

        $planning = $this->planningRepository->findOneBy(['projet' => $projet]);
        
        $approv = $projet->getApprovisionnement();

        $element = $this->elementRepository->find($ide);

        $mouvements = $element->getMouvements();

        $execfin = $this->tauxexecfinRepository->findOneBy(['projet' => $projet]);

        $execphys = $this->tauxexecphysRepository->findOneBy(['projet' => $projet]);

        $totalapprov = 0;
        $totalconso = 0;

        foreach ($mouvements as $mouv){
            if ($mouv->getType() == 'Approvisionnement'){
                $totalapprov += $mouv->getQuantite();
            }

            if ($mouv->getType() == 'Consommation'){
                $totalconso += $mouv->getQuantite();
            }
        }

        return $this->render('HTML/mouvement.html.twig', [
            'projet' => $projet,
            'user' => $user,
            'planning' => $planning,
            'approv' => $approv,
            'element' => $element,
            'execfin' => $execfin,
            'execphys' => $execphys,
            'mouvements' => $mouvements,
            'totalapprov' => $totalapprov,
            'totalconso' => $totalconso,
        ]);
    }

    #[Route('/projets/{id}/details/approvisionnement/element/{ide}/addmouvement', name:'Add-Mouvement')]
    public function addmouvement(Request $request, $id, $ide){
        $user = $this->getUser();

        $projet = $this->projetRepository->find($id);

        if ($user->getEmail() != $projet->getUser()->getEmail()){
            return $this->redirectToRoute('app_projets');
        }

        $planning = $this->planningRepository->findOneBy(['projet' => $projet]);
        
        $approv = $projet->getApprovisionnement();

        $element = $this->elementRepository->find($ide);

        $execfin = $this->tauxexecfinRepository->findOneBy(['projet' => $projet]);

        $execphys = $this->tauxexecphysRepository->findOneBy(['projet' => $projet]);

        $error = null;

        $mouvement = new Mouvement();
        $mouvement->setDate(new DateTimeImmutable());

        $form = $this->createForm(MouvementFormType::class, $mouvement);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            if ($form->get('type')->getData() == 'Consommation' && $form->get('quantite')->getData() > $element->getStockRestant()){
                $error = 'Impossible d\'enregistrer ce mouvement.';
            }

            else{
                $mouvement = $form->getData();

                if ($form->get('type')->getData() == 'Approvisionnement'){
                    $stock = $element->getStockRestant();
                    $stock += $form->get('quantite')->getData();
                    $element->setStockRestant($stock);
                }

                if ($form->get('type')->getData() == 'Consommation'){
                    $stock = $element->getStockRestant();
                    $stock -= $form->get('quantite')->getData();
                    $element->setStockRestant($stock);
                }

                $element->addMouvement($mouvement);

                $this->entityManager->persist($mouvement);
                $this->entityManager->flush();

                return $this->redirectToRoute('Mouvement', ['id' => $id, 'ide' => $ide]);
            }
        }

        return $this->render('mouvement/create.html.twig', [
            'projet' => $projet,
            'user' => $user,
            'planning' => $planning,
            'approv' => $approv,
            'element' => $element,
            'execfin' => $execfin,
            'execphys' => $execphys,
            'form' => $form->createView(),
            'error' => $error,
        ]);
    }

    #[Route('/projets/{id}/details/presence', name:'Presence')]
    public function presence($id){
        $user = $this->getUser();

        $projet = $this->projetRepository->find($id);

        if ($user->getEmail() != $projet->getUser()->getEmail()){
            return $this->redirectToRoute('app_projets');
        }

        $planning = $this->planningRepository->findOneBy(['projet' => $projet]);
        
        $approv = $projet->getApprovisionnement();

        $execfin = $this->tauxexecfinRepository->findOneBy(['projet' => $projet]);

        $execphys = $this->tauxexecphysRepository->findOneBy(['projet' => $projet]);

        $liste = $projet->getPresences();

        return $this->render('HTML/presence.html.twig', [
            'projet' => $projet,
            'user' => $user,
            'planning' => $planning,
            'approv' => $approv,
            'execfin' => $execfin,
            'execphys' => $execphys,
            'liste' => $liste,
        ]);
    }

    #[Route('/projets/{id}/details/presence/generate', name:'Generate-Presence')]
    public function generatePresence($id){
        $user = $this->getUser();

        $projet = $this->projetRepository->find($id);

        if ($user->getEmail() != $projet->getUser()->getEmail()){
            return $this->redirectToRoute('app_projets');
        }

        $today = new DateTime('now');

        $liste = $projet->getPresences();

        foreach($liste as $pres){
            if ($today->format('d/m/Y') == $pres->getDate()->format('d/m/Y')){
                return $this->redirectToRoute('Presence-Ouvriers', ['id' => $id, 'idp' => $pres->getId()]);
            }

            else{
                continue;
            }
        }

        $presence = new Presence();
        $presence->setDate($today);

        $projet->addPresence($presence);

        $this->entityManager->persist($presence);
        $this->entityManager->flush();

        return $this->redirectToRoute('Presence-Ouvriers', ['id' => $id, 'idp' => $presence->getId()]);
    }

    #[Route('/projets/{id}/details/presence/{idp}', name:'Presence-Ouvriers')]
    public function listepresence($id, $idp){
        $user = $this->getUser();

        $projet = $this->projetRepository->find($id);

        if ($user->getEmail() != $projet->getUser()->getEmail()){
            return $this->redirectToRoute('app_projets');
        }

        $planning = $this->planningRepository->findOneBy(['projet' => $projet]);
        
        $approv = $projet->getApprovisionnement();

        $execfin = $this->tauxexecfinRepository->findOneBy(['projet' => $projet]);

        $execphys = $this->tauxexecphysRepository->findOneBy(['projet' => $projet]);

        $liste = $this->presenceRepository->find($idp);

        $ouvriers = $liste->getOuvriers();

        return $this->render('HTML/ouvriers.html.twig', [
            'projet' => $projet,
            'user' => $user,
            'planning' => $planning,
            'approv' => $approv,
            'execfin' => $execfin,
            'execphys' => $execphys,
            'liste' => $liste,
            'ouvriers' => $ouvriers,
        ]);
    }

    #[Route('/projets/{id}/details/presence/{idp}/ajouter', name:'Presence-Ajouter-Ouvriers')]
    public function addOuvrier(Request $request, $id, $idp){
        $user = $this->getUser();

        $projet = $this->projetRepository->find($id);

        if ($user->getEmail() != $projet->getUser()->getEmail()){
            return $this->redirectToRoute('app_projets');
        }

        $planning = $this->planningRepository->findOneBy(['projet' => $projet]);
        
        $approv = $projet->getApprovisionnement();

        $execfin = $this->tauxexecfinRepository->findOneBy(['projet' => $projet]);

        $execphys = $this->tauxexecphysRepository->findOneBy(['projet' => $projet]);

        $liste = $this->presenceRepository->find($idp);

        $ouvrier = new Ouvrier();

        $form = $this->createForm(OuvrierFormType::class, $ouvrier);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $ouvrier = $form->getData();

            $liste->addOuvrier($ouvrier);

            $this->entityManager->persist($ouvrier);
            $this->entityManager->flush();

            return $this->redirectToRoute('Presence-Ajouter-Ouvriers', ['id' => $id, 'idp' => $idp]);
        }

        return $this->render('ouvrier/create.html.twig', [
            'projet' => $projet,
            'user' => $user,
            'planning' => $planning,
            'approv' => $approv,
            'execfin' => $execfin,
            'execphys' => $execphys,
            'liste' => $liste,
            'form' => $form->createView(),
        ]);
    }
}