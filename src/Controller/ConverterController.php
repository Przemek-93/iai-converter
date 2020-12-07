<?php

namespace App\Controller;

use App\Crud\ConvertedFileCrud;
use App\Crud\ConverterCrud;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConverterController extends AbstractController
{
    protected ConverterCrud $converterCrud;
    protected ConvertedFileCrud $convertedFileCrud;

    public function __construct(
        ConverterCrud $converterCrud,
        ConvertedFileCrud $convertedFileCrud
    ) {
        $this->converterCrud = $converterCrud;
        $this->convertedFileCrud = $convertedFileCrud;
    }

    /**
     * @Route("/converter", name="converter_main_view")
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
     * @Route("/converter/download", name="converter_download_view", methods={"GET"})
     */
    public function download(Request $request): Response
    {
        $convertedFiles = $this->convertedFileCrud->getAllFiles();

        return $this->render(
            'converter/download_converter.html.twig',
            [
                'convertedFiles' => $convertedFiles
            ]
        );
    }

    /**
     * @Route("/converter/add", name="converter_add_entry", methods={"POST"})
     */
    public function addConverter(Request $request): RedirectResponse
    {
        $this->converterCrud->createConverter($request);

        return $this->redirectToRoute('converter_add_view');
    }
}
