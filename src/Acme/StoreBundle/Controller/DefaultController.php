<?php

namespace Acme\StoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Acme\StoreBundle\Document\Product;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * @Route("/read_one/{id}", name="read_one")
     */
    public function showAction($id)
    {
        $product = $this->get('doctrine_mongodb')
            ->getRepository('AcmeStoreBundle:Product')
            ->findOneById($id);

        if (!$product) {
            throw $this->createNotFoundException('No product found for id '.$id);
        }

        return new Response(dump($product));
    }

    /**
     * @Route("/read_all", name="read_all")
     */
    public function showAllAction()
    {
        $product = $this->get('doctrine_mongodb')
            ->getRepository('AcmeStoreBundle:Product')
            ->findAll();

        if (!$product) {
            throw $this->createNotFoundException('No products found');
        }

        return $this->render('AcmeStoreBundle:Default:alllist.html.twig', ['list' => $product]);
    }


    /**
     * @Route("/add")
     */
    public function addAction(Request $request)
    {

        $product = new Product();
        $product->setName('Enter name');
        $product->setPrice(0.00);

        $form = $this->createFormBuilder($product)
            ->add('name', TextType::class)
            ->add('price', MoneyType::class)
            ->add('save', SubmitType::class, array('label' => 'Create Task'))
            ->getForm();


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();

            $dm = $this->get('doctrine_mongodb')->getManager();
            $dm->persist($product);
            $dm->flush();

            return $this->redirectToRoute('read_all');
        }


        return $this->render('AcmeStoreBundle:Default:add.html.twig', ['form' => $form->createView()]);
    }



}
