<?php

namespace App\Controller\Secure;

use App\Entity\Category;
use App\Entity\Subcategory;
use App\Form\SubcategoryType;
use App\Helpers\FileUploader;
use App\Repository\SubcategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

 /**
 * @Route("/category")
 */
class CrudSubcategoryController extends AbstractController
{
    /**
     * @Route("/{id_category}/subcategory", name="secure_crud_subcategory_index", methods={"GET"})
     */
    public function index(int $id_category,EntityManagerInterface $em,SubcategoryRepository $subcategoryRepository, PaginatorInterface $pagination, Request $request): Response
    {
        /** @var Category $objCategory */
        $objCategory = $em->getRepository(Category::class)->find($id_category);
        $data =$subcategoryRepository->findBy(['categoryId'=>$id_category]);
        $paginator = $pagination->paginate(
            $data,
            $request->query->getInt('page', $request->get("page") || 1), /*page number*/
            15, /*limit per page*/
            ['align' => 'center', 'style' => 'bottom',]
        );
        return $this->render('secure/crud_subcategory/index.html.twig', [
            'subcategories' => $paginator,
            'category_name'=>$objCategory->getName(),
            'id_category'=>$id_category
        ]);
    }

    /**
     * @Route("/{id_category}/subcategory/new", name="secure_crud_subcategory_new", methods={"GET","POST"})
     */
    public function new(int $id_category,EntityManagerInterface $em,Request $request,FileUploader $fileUploader): Response
    {
        /** @var Category $objCategory */
        $objCategory = $em->getRepository(Category::class)->find($id_category);
        $subcategory = new Subcategory();
        $form = $this->createForm(SubcategoryType::class, $subcategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $imageFileName = $fileUploader->upload($imageFile);
                $subcategory->setImage('uploads/images/'.$imageFileName);
            }
            $subcategory->setCategoryId($objCategory);
            $entityManager->persist($subcategory);
            $entityManager->flush();

            return $this->redirectToRoute('secure_crud_subcategory_index', ['id_category'=>$id_category], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('secure/crud_subcategory/new.html.twig', [
            'subcategory' => $subcategory,
            'form' => $form,
            'id_category'=>$id_category
        ]);
    }

    /**
     * @Route("/subcategory/{id}", name="secure_crud_subcategory_show", methods={"GET"})
     */
    public function show(Subcategory $subcategory): Response
    {
        return $this->render('secure/crud_subcategory/show.html.twig', [
            'subcategory' => $subcategory,
            'id_category'=>$subcategory->getCategoryId()->getId()
        ]);
    }

    /**
     * @Route("/subcategory/{id}/edit", name="secure_crud_subcategory_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Subcategory $subcategory, FileUploader $fileUploader): Response
    {
        $form = $this->createForm(SubcategoryType::class, $subcategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $imageFileName = $fileUploader->upload($imageFile);
                $subcategory->setImage('uploads/images/'.$imageFileName);
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('secure_crud_subcategory_index', ['id_category'=>$subcategory->getCategoryId()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('secure/crud_subcategory/edit.html.twig', [
            'subcategory' => $subcategory,
            'form' => $form,
            'id_category'=>$subcategory->getCategoryId()->getId()
        ]);
    }

    /**
     * @Route("/subcategory/{id}", name="secure_crud_subcategory_delete", methods={"POST"})
     */
    public function delete(Request $request, Subcategory $subcategory): Response
    {
        $id_category = $subcategory->getCategoryId()->getId();
        if ($this->isCsrfTokenValid('delete'.$subcategory->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($subcategory);
            $entityManager->flush();
        }

        return $this->redirectToRoute('secure_crud_subcategory_index', ['id_category'=>$id_category], Response::HTTP_SEE_OTHER);
    }
}
