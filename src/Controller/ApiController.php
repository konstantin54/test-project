<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Entity\Author;
use App\Entity\Book;

use App\Service\ApiServices;


class ApiController extends AbstractController
{
    /**
     * @Route("/api/v1/books/list", name="api_books")
     */
    //Список книг
    public function list(ApiServices $ApiServices, Request $request)
    {
        //проверка на доступ
        if(!$ApiServices -> checkAccess($request)) return new Response('', Response::HTTP_FORBIDDEN);
            
        $books = $this->getDoctrine()->getRepository('App:Book')->findAll();

        $booksArray = array();
        foreach ($books as $book) {
           $addArray = array('id' => $book -> getId(), 'name' => $book -> getName(), 'author' => $book -> getAuthor() -> getName()); 
           array_push($booksArray, $addArray); 
        };
        
        $json = json_encode($booksArray, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $response = new Response($json);
        $response->headers->set('Content-Type', 'application/json');
       
        return $response;
    }

    /**
     * @Route("/api/v1/books/by-{id}", name="api_book")
     */
    //Книга
    public function book($id, ApiServices $ApiServices, Request $request)
    {
        //проверка на доступ
        if(!$ApiServices -> checkAccess($request)) return new Response('', Response::HTTP_FORBIDDEN);
        
        $book = $this->getDoctrine()->getRepository('App:Book')->find($id);

        $bookArray = null;
        if($book) {
            $bookArray['name'] = $book -> getName();
            $bookArray['author'] = $book -> getAuthor() -> getName();
        };
     
        $json = json_encode($bookArray, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $response = new Response($json);
        $response->headers->set('Content-Type', 'application/json');
       
        return $response;
    }


    /**
     * @Route("/api/v1/books/by-{id}", name="api_book")
     */
    //Обновление данных о книге
    public function update(ApiServices $ApiServices, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        //проверка на доступ
        if(!$ApiServices -> checkAccess($request)) return new Response('', Response::HTTP_FORBIDDEN);
        
        $bookArray = null;
        $reqArray = $_POST;
        if(isset($reqArray['id']) && isset($reqArray['name']) && isset($reqArray['author'])){
                $book = $this->getDoctrine()->getRepository('App:Book')->find($reqArray['id']);
                if($book){
                    $book -> setName($reqArray['name']);
                    $author = $this->getDoctrine()->getRepository('App:Author')->findOneByName($reqArray['author']);    
                    if(!$author){
                        $author = new Author();
                        $author -> setName($reqArray['author']);
                        $em->persist($author);
                        $em->flush();
                    };
                    $book -> setAuthor($author);
                    $em->flush();
                    $bookArray['id'] = $book -> getId();
                    $bookArray['name'] = $book -> getName();
                    $bookArray['author'] = $book -> getAuthor() -> getName();
                };
        };

        $json = json_encode($bookArray, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $response = new Response($json);
        $response->headers->set('Content-Type', 'application/json');
       
        return $response;

    }


    /**
     * @Route("/api/v1/books/{id}", name="api_delete")
     */
    //Удаление книги
    public function delete($id, ApiServices $ApiServices, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $resultArray = null;

        //проверка на доступ
        if(!$ApiServices -> checkAccess($request)) return new Response('', Response::HTTP_FORBIDDEN);
     
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE'){
             $book = $this->getDoctrine()->getRepository('App:Book')->find($id);
             if($book){
                 $em -> remove($book);
                 $em -> flush();
                 $resultArray = array('deleted');
             };
        };

        $json = json_encode($resultArray, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $response = new Response($json);
        $response->headers->set('Content-Type', 'application/json');
       
        return $response;

    }





}
