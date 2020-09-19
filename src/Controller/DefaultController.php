<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Author;
use App\Entity\Book;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="main")
     */
    public function index()
    {


        return $this->render('index.html.twig');
    }


    /**
     * @Route("/generate", name="generate")
     */
    //Генерирование 100.000 книг и 2 авторов
    public function generate()
    {

    	$ok = 0;
    	$em = $this->getDoctrine()->getManager();
    	if(isset($_GET['ok'])) $ok = $_GET['ok'];

    	//Заполняем базу
    	if($ok == 1){

    		$books = $this->getDoctrine()->getRepository('App:Book')->findAll();
    		if(sizeof($books) < 100000){
    			 //Добавлем 100.000 книг автора
    			 $author = $this->getDoctrine()->getRepository('App:Author')->findOneBy(array('name' => 'Дарья Донцова'));
    			 if(!$author){
    			 	$author = new Author;
    			 	$author -> setName('Дарья Донцова');
    				$em->persist($author);
    			 };
    			 $num = 100001 - sizeof($books);
    			 for($i = 1; $i <= $num; $i++ ){
    			 	 $newBook[$i] = new Book;
    			 	 $newBook[$i] -> setAuthor($author);
    			 	 $newBook[$i] -> setName('Книга №'.$i);
    			 	 $em->persist($newBook[$i]);
    			 };	
    		};

    		$authors = $this->getDoctrine()->getRepository('App:Author')->findAll();
    		if(sizeof($authors) < 2){
    			//Добавляем 2 автора
    			$author1 = new Author;
    			$author1 -> setName('Иван Иванов');
    			$em->persist($author1);
    			$author2 = new Author;
    			$author2 -> setName('John Smith');
    			$em->persist($author2);
    		};
    	
    		$em -> flush();
            return $this->redirectToRoute('admin_books');
    	};




        return $this->render('generate.html.twig', [
            'ok' => $ok,
        ]);
    }

}
