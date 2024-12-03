<?php

namespace App\Controller;

use App\Entity\Student;
use App\Entity\Log;
use App\Form\StudentType;
use App\Repository\StudentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/student')]
final class StudentController extends AbstractController
{

    #[Route(name: 'app_student_index', methods: ['GET'])]
    public function index(StudentRepository $studentRepository): Response
    {
        return $this->render('student/index.html.twig', [
            'students' => $studentRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_student_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $student = new Student();
        $form = $this->createForm(StudentType::class, $student);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $student->setcreatedAt(new \DateTimeImmutable());
            $student->setupdatedAt(new \DateTimeImmutable());

            $photoFile = $form->get('photo')->getData();
            if ($photoFile) {
       
                    $newFilename = uniqid() . '.' . $photoFile->guessExtension();

                    $photoFile->move(
                        $this->getParameter('photos_directory'),
                    $newFilename
                    );
              
                    $student->setPhoto($newFilename);
            }

            $entityManager->persist($student);
            $entityManager->flush();

            //Ici on crée un objet log dans le new du StudentController
            $log = new Log($entityManager);
            $log->log(
                $this->getUser(),
                'create',
                'created student id '.$student->getId());

            return $this->redirectToRoute('app_student_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('student/new.html.twig', [
            'student' => $student,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_student_show', methods: ['GET'])]
    public function show(Student $student, EntityManagerInterface $entityManager): Response
    {
        //Ici on crée un objet log dans le show du StudentController
        $log = new Log($entityManager);
        $log->log(
            $this->getUser(),
            'read',
            'read student id '.$student->getId());

        return $this->render('student/show.html.twig', [
            'student' => $student,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_student_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Student $student, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(StudentType::class, $student);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $student->setupdatedAt(new \DateTimeImmutable());

            $photoFile = $form->get('photo')->getData();
            if ($photoFile) {
       
                    $newFilename = uniqid() . '.' . $photoFile->guessExtension();

                    $photoFile->move(
                        $this->getParameter('photos_directory'),
                    $newFilename
                    );
              
                    $student->setPhoto($newFilename);
            }
            
            $entityManager->flush();

            //Ici on crée un objet log dans l'edit du StudentController
            $log = new Log($entityManager);
            $log->log(
                $this->getUser(),
                'update',
                'updated student id '.$student->getId());

            return $this->redirectToRoute('app_student_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('student/edit.html.twig', [
            'student' => $student,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_student_delete', methods: ['POST'])]
    public function delete(Request $request, Student $student, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$student->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($student);
            $entityManager->flush();
        }

        //Ici on crée un objet log dans le delete du StudentController
        $log = new Log($entityManager);
        $log->log(
            $this->getUser(),
            'delete',
            'deleted student id '.$student->getId());

        return $this->redirectToRoute('app_student_index', [], Response::HTTP_SEE_OTHER);
    }
}
