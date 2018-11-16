<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use \Symfony\Component\Form\Extension\Core\Type\TextType;
use function Sodium\add;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{
    /**
     * @Route("/blog", name="blog")
     */
    public function index(ArticleRepository $repo)
    {
        //$repo = $this->getDoctrine()->getRepository(Article::class);

        $articles= $repo->findAll();

        return $this->render('blog/index.html.twig', [
            'controller_name' => 'BlogController',
            'articles' => $articles,
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/", name="home")
     */
    public function home(){


        return $this->render('blog/home.html.twig', [
            'title' => "bienvenue"
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/blog/new", name= "blog_create")
     * @Route("/blog/{id}/edit", name= "blog_edit")
     */
    public function form(Article $article = null,Request $request, ObjectManager $manager){
        //$article = new Article();

        if(!$article){
            $article= new Article();
        }

        $form= $this->createFormBuilder($article)
                    /*->add('title', TextType::class, [
                        'attr' => [
                            'placeholder'=>"titre de l'article",
                            'class'=>"form-control"
                        ]
                    ])
                    ->add('content', TextareaType::class, [
                        'attr' => [
                            'placeholder'=>"titre de l'article",
                            'class'=>"form-control"
                            ]
                        ])
                    ->add('image', TextType::class, [
                        'attr' => [
                            'placeholder'=>"titre de l'article",
                            'class'=>"form-control"
                            ]
                        ])
                    ->getForm();*/
                    ->add('title')
                    ->add('category', EntityType::class,[
                        'class' => Category::class,
                        'choice_label'=> 'title'
                        ])
                    ->add('content')
                    ->add('image', TextType::class)
                    /*->add('save', SubmitType::class, [
                        'label'=> 'Enregistrer'
                    ])*/
                    ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            if(!$article->getCreatedAt()){
                $article->setCreatedAt(new \DateTime());
            }

            $manager->persist($article);
            $manager->flush();

            return $this->redirectToRoute('blog_show', ['id'=>$article->getId()]);
        }

        return $this->render('blog/create.html.twig', [
            'formArticle' => $form->createView()
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/blog/article/{id}", name="blog_show")
     */
    public function show(Article $article, ArticleRepository $repo, $id, Request $request, ObjectManager $manager){
        //$repo = $this->getDoctrine()->getRepository(Article::class);
        $comment= new Comment();

        $form= $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $comment->setCreatedAt(new \DateTime());
            $comment->setArticle($article);

            $manager->persist($comment);

            $manager->flush();

            return $this->redirectToRoute('blog_show',[
                'id'=> $article->getId(),
            ]);
        }

        $article = $repo->find($id);

        return $this->render('blog/show.html.twig',[
            'article'=> $article,
            'commentForm'=> $form->createView(),
        ]);
    }


}
