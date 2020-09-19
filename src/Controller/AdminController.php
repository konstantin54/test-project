<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\Author;
use App\Entity\Book;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index()
    {



        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }


    /**
     * @Route("/admin/books", name="admin_books")
     */
    //Книги
    public function books()
    {
    	$em = $this->getDoctrine()->getManager();	
        $message = '';

        //Удаляем книгу
        if(isset($_GET['del'])){
            $delBook = $this->getDoctrine()->getRepository('App:Book')->find($_GET['del']);    
            if($delBook){
                $em -> remove($delBook);
                $em -> flush();
                $message = 'Книга удалена';
            } else {
                $message = 'Книга не найдена';
            };
        };

    	$booksAll = $this->getDoctrine()->getRepository('App:Book')->findAll();
    	
    	$page = 1; if(isset($_GET['page'])) $page = $_GET['page'];
    	$onPage = 200;
        $pagesNum = ceil(sizeof($booksAll)/$onPage);
        $skip = ($page - 1) * $onPage;
      

  		$query = $em->createQuery("SELECT b FROM App:Book b ORDER BY b.id DESC")
  				->setFirstResult($skip)->setMaxResults($onPage);
        $books = $query->getResult();


        return $this->render('admin/books.html.twig', [
            'books' => $books,
            'page' => $page,
            'pagesNum' => $pagesNum,
            'message' => $message,
        ]);
    }

    /**
     * @Route("/admin/authors", name="admin_authors")
     */
    //Авторы
    public function authors()
    {
        $em = $this->getDoctrine()->getManager();   
        $message = '';

        //Удаляем автора
        if(isset($_GET['del'])){
            $delAuthor = $this->getDoctrine()->getRepository('App:Author')->find($_GET['del']);
                
            if($delAuthor && sizeof($delAuthor -> getBooks()) == 0){
                $message = 'Автор <b>'.$delAuthor -> getName().'</b> удалён';
                $em -> remove($delAuthor);
                $em -> flush();
                
            } elseif($delAuthor && sizeof($delAuthor -> getBooks()) > 0) {
                $message = 'Необходимо сначала удалить книги автора';
            } else {
                $message = 'Автор не найден';
            };
        };

        $authors = $this->getDoctrine()->getRepository('App:Author')->findAll();
    

        return $this->render('admin/authors.html.twig', [
            'authors' => $authors,
            'message' => $message,
        ]);
    }



    /**
     * @Route("/admin/add/book", name="admin_addbook")
     */
    //Добавить книгу + Редактировать книгу
    public function addbook(Request $request)
    {
         $em = $this->getDoctrine()->getManager();
         $authors = $this->getDoctrine()->getRepository('App:Author')->findAll();
         foreach ($authors as $author) {
             $authorsArray[$author -> getName()] = $author -> getId();
         }

         $book = null;
         $defaultData = null;
         $red = 0;

         //Редактируем существующую книгу
         if(isset($_GET['id'])){
            $red = $_GET['id']; 
            $book = $this->getDoctrine()->getRepository('App:Book')->find($red);
            if($book){
                $defaultData['name'] = $book -> getName();
                $defaultData['author'] = $book -> getAuthor() ->getId();
            } else {
                $red = 0;
            };
         };

         $form = $this->createFormBuilder($defaultData)
                ->add('name', TextType::class, array('required' => true))
                ->add('author', ChoiceType::class, array('choices' => $authorsArray, 'required' => true))
                ->getForm();
         $form->handleRequest($request);   

        if ($form->isSubmitted() && $form->isValid()) {

            if(!$book) $book = new Book();
            $data_form = $form->getData();
            $book -> setName($data_form['name']);
            $book -> setAuthor($this->getDoctrine()->getRepository('App:Author')->find($data_form['author']));
            $em->persist($book);
            $em->flush();
            return $this->redirectToRoute('admin_books');
        }    

        return $this->render('admin/add_book.html.twig', [
            'form' => $form->createView(),
            'red' => $red,
        ]);
    }



    /**
     * @Route("/admin/add/author", name="admin_addauthor")
     */
    //Добавить автора + Редактировать автора
    public function addauthor(Request $request)
    {
         $em = $this->getDoctrine()->getManager();

         $author = null;
         $defaultData = null;
         $red = 0;

         //Редактируем автора
         if(isset($_GET['id'])){
            $red = $_GET['id']; 
            $author = $this->getDoctrine()->getRepository('App:Author')->find($red);
            if($author){
                $defaultData['name'] = $author -> getName();
            } else {
                $red = 0;
            };
         };

         $form = $this->createFormBuilder($defaultData)
            ->add('name', TextType::class, array('required' => true))
            ->getForm();
         $form->handleRequest($request);   

        if ($form->isSubmitted() && $form->isValid()) {
            if(!$author) $author = new Author();
            $data_form = $form->getData();
            $author -> setName($data_form['name']);
            $em->persist($author);
            $em->flush();
            return $this->redirectToRoute('admin_authors');
        }    

        return $this->render('admin/add_author.html.twig', [
            'form' => $form->createView(),
             'red' => $red,
        ]);
    }

    /**
     * @Route("/admin/api", name="admin_api")
     */
    //Описание API
    public function api()
    {
        
        return $this->render('admin/api.html.twig');
    }

}
