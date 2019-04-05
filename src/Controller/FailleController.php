<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Production;
use App\Entity\User;
use App\Form\FileType;
use App\Form\UserType;
use App\Service\BrokenConfigLoader;
use App\Service\SiteMap;
use App\Service\UserProvider;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class FailleController extends AbstractController
{

    //////////////////////// CSRF //////////////////////




    /**
     * @Route("/inscriptionCSRF", name="app_register_csrf")
     */
    public function registerCSRF(Request $request, UserPasswordEncoderInterface $passwordEncoder, ObjectManager $manager, User $user = null): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, [
            'method' => 'GET',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $user->getPassword();
            $user->setPassword($passwordEncoder->encodePassword(
                $user,
                $password
            ));
            $manager->persist($user);
            $manager->flush();
            return $this->redirectToRoute('home');
        }

        return $this->render('faille/registerCsrf.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/test_csrf", name="csrf")
     */
    public function testCsrfAction()
    {
        $response = new Response();
        $result = $this->renderView('faille/testCsrf.html.twig');
        if (isset($_GET['url'])) {
            $redirect_url = $_GET['url'];
            $response->headers->set('Location', $redirect_url);
        }

        $response->setContent($result);
//        return $this->render('security/testCsrf.html.twig');
        return $response;
    }



    /**
     * @Route("/various/missing-csrf-protection")
     *
     * @param Request $request
     */
    public function missingCsrfProtection(Request $request)
    {
        $user = new User();
        $user->setEmail($request->get('email'));

        $this->getDoctrine()->getManager()->persist($user);
        $this->getDoctrine()->getManager()->flush();
    }



    //////////////////// XSS /////////////////////

    /**
     * @Route("/xss/basic")
     */
    public function basicCrossSiteScripting()
    {
        echo $_GET['test'];
    }

    /**
     * @Route("/xss/basic_variable")
     */
    public function basicCrossSiteScriptingWithVariable()
    {
        $a = $_GET['test'];
        echo $a;
    }

    /**
     * @Route("/xss/advanced")
     *
     * @param Request $request
     * @return Response
     */
    public function advancedCrossSiteScripting(Request $request)
    {
        return $this->render('xss/advanced.html.twig', [
            'output' => $request->get('test')
        ]);
    }






    ////////////////////////////// SQL //////////////////



    /**
     * @Route("injection/basic")
     */
    public function basicInjection()
    {
        mysqli_connect('localhost', 'root', 'root');
        mysqli_select_db('test', 'testdn');

        $user = $_POST['user'];
        $pass = $_POST['password'];
        $re = mysqli_query('test', "select * from zend_adminlist where user_name = '$user' and password = '$pass'");
        $re2 = mysqli_query('test', "select * from zend_adminlist where user_name = '".$_POST['user']."' and password = '".$_POST['password']."'");

        if (mysqli_num_rows($re) == 0) {
            echo '0';
        } else {
            echo '1';
        }
    }

    /**
     * @Route("injection/basic-orm")
     * @param ObjectManager $entityManager
     */
    public function basicOrmInjection(ObjectManager $entityManager)
    {
        $query = $entityManager->createQuery('SELECT * FROM User u WHERE u.email = ' . $_GET['user']);
        $users = $query->getResult();

        $user = $_GET['user'];
        $query2 = $entityManager->createQuery("SELECT * FROM User u WHERE u.email = '$user'");
        $users2 = $query2->getResult();

    }

    /**
     * @Route("injection/intermediate-orm")
     * @param Request $request
     */
    public function intermediateOrmInjection(Request $request)
    {
        $mail="";
        $users = $this->getDoctrine()->getRepository(User::class)->createQueryBuilder('u')
//            ->andwhere('u.email = :query')
//            ->setParameter(':query', $mail)
            ->where('u.email = ' . $request->get('mail'))
            ->getQuery()
            ->getResult();

        $var = $request->get('user');
        $users2 = $this->getDoctrine()->getRepository(User::class)->createQueryBuilder('u')
            ->where("u.email = '$var'")
            ->getQuery()
            ->getResult();

        dump($users2); die();
    }

    /**
     * @Route("injection/advanced-orm")
     * @param Request $request
     * @param UserProvider $userProvider
     */
    public function advancedOrmInjection(Request $request, UserProvider $userProvider)
    {
        $users = $userProvider->provideUsers($request->get('user'));
        return $this->json($users);
    }




    /////////////////// deserialize /////////////////////////////////
    /**
     * @param Request $request
     */
    public function simpleRemoteCodeExecution(Request $request)
    {
        $configFile = $request->request->get('filename');
        if (file_exists($configFile)) {
            $config = unserialize(file_get_contents($configFile));
        }

        if (file_exists($request->request->get('filename'))) {
            $config = unserialize(file_get_contents($request->request->get('filename')));
        }
    }

    /**
     * @Route("/serialize", name="serialize")
     */
    public function serializeAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository(User::class);
        $productions = $repository->findAll();

        $encoders = array(new XmlEncoder());
        $normalizers = array(new ObjectNormalizer());

        $serializer = new Serializer($normalizers, $encoders);
        $productionsSerialized = $serializer->serialize($productions, 'xml');
        $file=fopen('newFile.xml', 'w') or die("Unable to open file!");
        fwrite($file, $productionsSerialized);
        die();
    }

    /**
     * @Route("/deserialize", name="deserialize")
     */
    public function deserializeAction(Request $request)
    {
        $file=fopen('file.xml', 'r') or die("Unable to open file!");
        $encoders = array(new XmlEncoder());
        $normalizers = array(new ObjectNormalizer());

        $res= null;
//        dump(fgets($file, 4096)); die();
        while (($buffer = fgets($file)) !== false) {
            $res .= $buffer;
        }

//        $res = substr($res)
//        dump($res); die();
//
//        $res = '<response>
//                    <item key="a0">
//                    <id>52</id>
//                    <email>userdemo0@example.com</email>
//                    <username>userdemo0@example.com</username>
//                    <roles>ROLE_USER</roles>
//                    <password>userdemo</password>
//                    <salt/>
//                </item></response>';
        $serializer = new Serializer($normalizers, $encoders);
        $productionsDeserialized = $serializer->deserialize($res, User::class, 'xml');

        dump($productionsDeserialized);
        die();
    }

    /**
     * @Route("/parser", name="parser")
     */
    public function parser(Request $request)
    {
        libxml_disable_entity_loader(false);
        $xml=simplexml_load_file("file.xml") or die("Error: Cannot create object");
        print_r($xml);
        dump($xml);
        die();
    }

    /**
     * @param Request $request
     */
    public function hiddenRemoteCodeExecution(Request $request, BrokenConfigLoader $configLoader)
    {
        return $this->json($configLoader->loadConfigFile($request->get('config')));
    }


    //////////////////// Various /////////////////////

    /**
     * @Route("/various/unsafe-redirect")
     */
    public function unsafeRedirect()
    {
        if ($_SESSION['user_logged_in'] !== true) {
            header('Location: /login.php');
        }

        $this->render('sensibleInformation.html.twig');
    }

    /**
     * @Route("/various/dynamic-globals")
     */
    public function dynamicGlobalsUsage()
    {
        $user = new \stdClass;

        $adminRights = $user->hasAdminRights();

        foreach ($_REQUEST as $var => $val) {
            $var = $val;
        }

        if ($adminRights) {
            $this->render('sensibleInformation.html.twig');
        }
    }

}
