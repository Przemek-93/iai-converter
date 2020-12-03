<?php

namespace App\Controller;

use App\Crud\ConverterCrud;
use App\Service\Converter\ConverterWorker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConverterController extends AbstractController
{
    protected ConverterCrud $converterCrud;
    protected ConverterWorker $converterWorker;

    public function __construct(
        ConverterCrud $converterCrud,
        ConverterWorker $converterWorker
    ) {
        $this->converterCrud = $converterCrud;
        $this->converterWorker = $converterWorker;
    }

    /**
     * @Route("/converter", name="main_view")
     */
    public function index(): Response
    {
        $converters = $this->converterCrud->getConverters();

        return $this->render(
            'converter/select_converter.html.twig',
            [
                'converters' => $converters
            ]
        );
    }

    /**
     * @Route("/converter/add", name="converter_add_view", methods={"GET"})
     */
    public function addView(): Response
    {
        return $this->render(
            'converter/add_converter.html.twig'
        );
    }

    /**
     * @Route("/converter/add", methods={"POST"})
     */
    public function addConverter(Request $request): RedirectResponse
    {
        $response = $this->converterCrud->createConverter($request);

        return $this->redirectToRoute('converter_add_view');
    }

    /**
     * @Route("/converter/upload", methods={"POST", "GET"})
     */
    public function uploadFile(Request $request): RedirectResponse
    {
        if ($request->getMethod() === 'POST') {
            $a = $request->files->get('fileToUpload');
            $this->converterWorker->convert(
                $request->get('converter'),
                $request->files->get('fileToUpload')
            );
        }

        return $this->redirectToRoute('main_view');
    }
}
