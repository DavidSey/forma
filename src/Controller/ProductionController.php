<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Picture;
use App\Entity\Production;
use App\Form\CommentType;
use App\Form\ProductionFormType;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProductionController extends AbstractController
{
    /**
     * @Route("/realisations", name="productions")
     */
    public function indexProductionsAction()
    {
        $repository = $this->getDoctrine()->getRepository(Category::class);
        $categories = $repository->findAll();

        return $this->render('production/index.html.twig', [
            'categories' => $categories
        ]);
    }

    /**
     * @Route("/realisation/new", name="production_new")
     */
    public function newProductionAction(Request $request, ObjectManager $manager)
    {
        $production = new Production();
        $form = $this->createForm(ProductionFormType::class, $production);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pictures = $production->getPictures();
            foreach ($pictures as $picture) {
                $picture = $this->imagePersist($picture);
                $picture->setIdProduction($production);
                $manager->persist($picture);
            }

            $repository = $this->getDoctrine()->getRepository(Category::class);
            $category = $repository->findFirst();

            $production->addCategory($category);
            $manager->persist($production);
            $manager->flush();
            return $this->redirectToRoute('productions');
        }


        return $this->render('production/new.html.twig', [
            'formProduction' => $form->createView()
        ]);
    }

    /**
     * @Route("/realisation/{title}", name="production_show")
     */
    public function showProductionAction($title, Request $request, ObjectManager $manager)
    {
//        $headers = $request->headers;
//        $reponse = new Response();
//        $reponse->headers->set('X-Frame-Options', 'DENY');
//        dump($reponse); die();
//        return reponse;

        $bdd = new \PDO('mysql:host=localhost;dbname=formation', 'root', 'root');
        $data = null;
        $reponse = $bdd->query('SELECT * FROM comment');
        $data = $reponse->fetchAll();

        $repository = $this->getDoctrine()->getRepository(Production::class);
        $production = $repository->findOneByName($title);

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $id = $production->getId();
            $bdd->exec("INSERT INTO comment(production_id, author, content) VALUE (".$id.", '".$_POST['comment']['author']. "', '". $_POST['comment']['content'] . "')");
            //todo:SQL
//            ','azerty') #
//            <script>alert("bonjour")</script>​
//            $sql = "INSERT INTO comment(production_id, author, content) VALUE (?, ?,  ?)";
//            $stmt = $bdd->prepare($sql);
//            $stmt->bindValue(1, $id)
//                ->bindValue(2,$_POST['comment']['author'])
//                ->bindValue(3, $_POST['comment']['content']);
//            $stmt->execute();

//            $comment->setProduction($production);
//            $manager->persist($comment);
//            $manager->flush();
//            return $this->redirectToRoute('production_show', array('title' => $production->getName()));
            return $this->render('production/show.html.twig', [
                'production' => $production,
                'formComment' => $form->createView(),
            ]);
        }

        return $this->render('production/show.html.twig', [
            'production' => $production,
            'formComment' => $form->createView(),
            'data' => $data
        ]);
    }

    /**
     * @return Picture
     */
    public function imagePersist(Picture $image)
    {
        if ($image->getFile() == null) {
            return $image;
        }

        /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
        $file = $image->getFile();

//        $fileName = md5(uniqid());
//        $imageName = $fileName . '.' . $file->getClientOriginalExtension();
        $fileName = $file->getClientOriginalName();

        try {
            $file->move(
                $this->getParameter('images_directory'),
                $fileName
            );
        } catch (FileException $e) {
            // ... handle exception if something happens during file upload
        }


        $image->setName($fileName);
        return $image;
    }
}
