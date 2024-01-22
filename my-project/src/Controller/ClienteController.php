<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Product;
use App\Form\ProductType;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Cliente;
use App\Entity\Emp;


class ClienteController extends AbstractController
{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/', name: 'app_index')]
    public function index(): Response
    {
        return $this->render('client/index1.html', [
            'controller_name' => 'ClienteController',
        ]);
    }

    #[Route('/clients/{page}', name: 'app_clients')]
    public function list(int $page = 1): Response
    {
        $clientsRepository = $this->em->getRepository(Cliente::class);

        // Número de clientes por página
        $clientsPerPage = 5;

        // Calcular el desplazamiento
        $offset = ($page - 1) * $clientsPerPage;

        // Contar el total de clientes
        $totalClients = $clientsRepository->count([]);

        // Calcular el total de páginas
        $totalPages = ceil($totalClients / $clientsPerPage);

        // Obtener los clientes paginados
        $clients = $clientsRepository->findBy([], null, $clientsPerPage, $offset);

        // Renderizar la plantilla "list.html.twig" con los resultados y la información de paginación
        return $this->render("client/index.html", [
            "resultados" => $clients,
            "currentPage" => $page,
            "totalPages" => $totalPages
        ]);
    }




    #[Route('/detail/{id}', name: 'client_show')]
    public function show(EntityManagerInterface $entityManager, int $id): Response
    {
        $client = $entityManager->getRepository(Cliente::class)->find($id);
        $empleado = $entityManager->getRepository(Emp::class);
        $emp = $empleado->find($client->getReprCod());

        return $this->render("client/detail.html", [
            "cliente" => $client,
            "empleado" => $emp
        ]);
    }

    #[Route('/delete/{id}', name: 'app_del')]
    public function del(EntityManagerInterface $entityManager, int $id): Response
    {
        $clientsRepository = $entityManager->getRepository(Cliente::class);
        $client = $clientsRepository->find($id);

        if (!$client) {
            return new Response('No existe el cliente con ID ' . $id);
        }

        $entityManager->remove($client);
        $entityManager->flush();

        // Redirect to /clients after successful deletion
        return $this->redirectToRoute('app_clients');
    }

    #[Route('/update/{id}', name: 'app_update')]
    public function update(EntityManagerInterface $entityManager, int $id): Response
    {
        $client = $entityManager->getRepository(Cliente::class)->find($id);

        // Renderizar la plantilla "upform.html" con los datos del registro del cliente
        return $this->render("client/upform.html", [
            "resultados" => $client,
        ]);
    }

    #[Route('/updateForm/{id}', name: 'app_update_process', methods: ['POST'])]
    public function updateProcess(EntityManagerInterface $entityManager, int $id): Response
    {
        $nombre = $_POST['nombre'];
        $direc = $_POST['direc'];
        $ciudad = $_POST['ciudad'];
        $estado = $_POST['estado'];
        $codPostal = $_POST['codPostal'];
        $area = $_POST['area'];
        $telefono = $_POST['telefono'];
        $reprCod = $_POST['reprCod'];
        $limiteCredito = $_POST['limiteCredito'];
        $observaciones = $_POST['observaciones'];

        // Get the entity manager

        $client = $entityManager->getRepository(Cliente::class)->find($id);

        // Update client data
        $client->setNombre($nombre);
        $client->setDirec($direc);
        $client->setCiudad($ciudad);
        $client->setEstado($estado);
        $client->setCodPostal($codPostal);
        $client->setArea($area);
        $client->setTelefono($telefono);
        $client->setReprCod($reprCod);
        $client->setLimiteCredito($limiteCredito);
        $client->setObservaciones($observaciones);

        // Persist changes to the database
        $this->em->persist($client);
        $this->em->flush();

        // Redirect to the clients page after the update
        return $this->redirectToRoute('app_clients');
    }

    #[Route('/insert', name: 'app_insert')]
    public function insert(): Response
    {

        // Renderizar la plantilla "form.html"
        return $this->render("client/form.html", [
            'controller_name' => 'ClienteController',
        ]);
    }



    #[Route('/add', name: 'app_add', methods: ['POST'])]
    public function add(EntityManagerInterface $entityManager): Response
    {
        $nombre = $_POST['nombre'] ?? '';
        $direc = $_POST['direc'] ?? '';
        $ciudad = $_POST['ciudad'] ?? '';
        $estado = $_POST['estado'] ?? '';
        $codPostal = $_POST['codPostal'] ?? '';
        $area = isset($_POST['area']) ? intval($_POST['area']) : 0;
        $telefono = $_POST['telefono'] ?? '';
        $reprCod = isset($_POST['reprCod']) ? intval($_POST['reprCod']) : 0;
        $limiteCredito = isset($_POST['limiteCredito']) ? intval($_POST['limiteCredito']) : 0;
        $observaciones = $_POST['observaciones'] ?? '';

        // Crear una nueva instancia de la entidad Cliente
        $client = new Cliente;

        // Configurar los datos del cliente
        $client->setNombre($nombre);
        $client->setDirec($direc);
        $client->setCiudad($ciudad);
        $client->setEstado($estado);
        $client->setCodPostal($codPostal);
        $client->setArea($area);
        $client->setTelefono($telefono);
        $client->setReprCod($reprCod);
        $client->setLimiteCredito($limiteCredito);
        $client->setObservaciones($observaciones);

        // Persistir los cambios en la base de datos
        $entityManager->persist($client);
        $entityManager->flush();

        // Redireccionar a la página de clientes después de la actualización
        return $this->redirectToRoute('app_clients');
    }
}
