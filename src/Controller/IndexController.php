<?php
namespace App\Controller;
use App\Entity\Article;
use App\Form\ArticleType;
use App\Entity\PropertySearch;
use App\Form\PropertySearchType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Category;
use App\Form\CategoryType;
use App\Entity\PriceSearch;
use App\Form\PriceSearchTpe;
use App\Form\PriceSearchType;

use App\Entity\CategorySearch;
use App\Form\CategorySearchType;

class IndexController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

    }
    
    #[Route('/', name: 'articles', methods: ['GET', 'POST'])]
    public function home(Request $request,EntityManagerInterface $entityManager)
    {
       // $articles = ['Article 1', 'Article 2', 'Article 3'];
       // return $this->render('index.html.twig', ['articles' => $articles]);
      //récupérer tous les articles de la table article de la BD
 // et les mettre dans le tableau $articles


 $propertySearch = new PropertySearch();
 $form = $this->createForm(PropertySearchType::class,$propertySearch);
 $form->handleRequest($request);
 //initialement le tableau des articles est vide,
 //c.a.d on affiche les articles que lorsque l'utilisateur
 //clique sur le bouton rechercher
 $articles= [];
 
 if ($form->isSubmitted() && $form->isValid()) {
    $nom = $propertySearch->getNom(); 
    if ($nom != "") {
        $articles = $entityManager->getRepository(Article::class)->findBy(['nom' => $nom]);
    } else {
        $articles = $entityManager->getRepository(Article::class)->findAll();
    }
}
return $this->render('articles/index.html.twig', ['articles' => $articles, 'form' => $form->createView()]);    }


    
    #[Route('/article/save', name: 'save', methods: ['GET'])]
    public function save(EntityManagerInterface $entityManager)
    {
        $entityManager = $entityManager; 
        $article = new Article();
        $article->setNom('Article 1');
        $article->setPrix(1000);
        
        $entityManager->persist($article);
        $entityManager->flush();
        
        return new Response('Article enregistré avec id ' . $article->getId());
    }
     #[Route('/article/new', name: 'new_article', methods: ['GET','POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager , CategoryRepository $categoryRepository)
    {
       $article = new Article();
       $categories = $categoryRepository->findAll();

        $form = $this->createFormBuilder($article)
            ->add('nom', TextType::class)
            ->add('prix', TextType::class)
            ->add('category', EntityType::class, [
            'class' => Category::class,
            'choices' => $categories,
            'choice_label' => 'titre',
            'label' => 'Catégorie',
        ])
            ->add('save', SubmitType::class, ['label' => 'Créer'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article = $form->getData();

            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('articles');
        }

        return $this->render('articles/new.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/article/{id}', name: 'show', requirements: ['id' => '\d+'])] // Validate ID format
public function show(int $id, EntityManagerInterface $entityManager) // Inject EntityManager
{
    $article = $entityManager->getRepository(Article::class)->find($id);

    if (!$article) {
        throw $this->createNotFoundException('Aucun article trouvé avec cet ID'); // Handle missing article
    }

    return $this->render('articles/show.html.twig', ['article' => $article]);
}

    /**
 * @Route("/article/edit/{id}", name="edit_article")
 * Method({"GET", "POST"})
 */
#[Route('/article/edit/{id}', name: 'edit_article')]
public function edit(Request $request, $id)
{
    $article = $this->entityManager->getRepository(Article::class)->find($id);

    if (!$article) {
        throw $this->createNotFoundException('Article non trouvé pour l\'id '.$id);
    }
    $form = $this->createForm(ArticleType::class, $article);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $this->entityManager->flush();
        return $this->redirectToRoute('articles');
    }

    return $this->render('articles/edit.html.twig', ['form' => $form->createView()]);
}
 

 
#[Route('/article/delete/{id}', name: 'delete_article', methods: ['GET','DELETE'], requirements: ['id' => '\d+'])]
public function delete(Request $request, int $id, EntityManagerInterface $entityManager)
{
    $article = $entityManager->getRepository(Article::class)->find($id);

    if (!$article) {
        throw $this->createNotFoundException('Aucun article trouvé avec cet ID'); // Handle missing article
    }

    $entityManager->remove($article);
    $entityManager->flush();

    // Assuming a JavaScript-based deletion request
    return $this->json(['success' => true]); // Return appropriate response based on request type
        return $this->render('articles/show.html.twig', ['form' => $form->createView()]);

  }

  /**
 * @Route("/category/newCat", name="new_category")
 * Method({"GET", "POST"})
 */
#[Route('/category/newCat', name: 'new_category', methods: ['GET','POST'])]
   public function newCategory(Request $request) {
        $category = new Category();
        $form = $this->createForm(CategoryType::class,$category);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();
            $this->entityManager->persist($category);
            $this->entityManager->flush();
        }
        return $this->render('articles/newCategory.html.twig',['form'=>$form->createView()]);
    }


    /**
 * @Route("/art_cat/", name="article_par_cat")
 * Method({"GET", "POST"})
 */
#[Route('/art_cat/', name: 'article_par_cat', methods: ['GET','POST'])]

 public function articlesParCategorie(Request $request) {
    $categorySearch = new CategorySearch();
    $form = $this->createForm(CategorySearchType::class,$categorySearch);
    $form->handleRequest($request);
    $articles= [];
    if($form->isSubmitted() && $form->isValid()) {
        $category = $categorySearch->getCategory();
        
        if ($category!="") {
            // Correction : Utiliser getArticle au lieu de getArticles
            $articles= $category->getArticle();
        } else {
            $articles= $this->getDoctrine()->getRepository(Article::class)->findAll();
        }
    }
    
    return $this->render('articles/articlesParCategorie.html.twig',[
        'form' => $form->createView(),
        'articles' => $articles
    ]);
}


/**
 * @Route("/art_prix/", name="article_par_prix")
 * Method({"GET"})
 */
 #[Route('/art_prix/', name: 'article_par_prix', methods: ['GET', 'POST'])]
    public function articlesParPrix(Request $request)
    {
        $priceSearch = new PriceSearch();
        $form = $this->createForm(PriceSearchType::class, $priceSearch);
        $form->handleRequest($request);

        $articles = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $minPrice = $priceSearch->getMinPrice();
            $maxPrice = $priceSearch->getMaxPrice();

            // Utilisez $this->entityManager->getRepository() pour accéder au référentiel
            $articles = $this->entityManager
                ->getRepository(Article::class)
                ->findByPriceRange($minPrice, $maxPrice);
        }

        return $this->render('articles/articlesParPrix.html.twig', [
            'form' => $form->createView(),
            'articles' => $articles,
        ]);
    }
}