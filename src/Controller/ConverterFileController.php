<?php

namespace App\Controller;

use App\Crud\ConvertedFileCrud;
use App\Service\Converter\ConverterWorker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class ConverterFileController extends AbstractController
{
    protected ConverterWorker $converterWorker;
    protected ConvertedFileCrud $convertedFileCrud;

    public function __construct(
        ConverterWorker $converterWorker,
        ConvertedFileCrud $convertedFileCrud
    ) {
        $this->converterWorker = $converterWorker;
        $this->convertedFileCrud = $convertedFileCrud;
    }

    /**
     * @Route("/converter/file/upload", name="converter_upload_file", methods={"POST", "GET"})
     */
    public function uploadFile(Request $request): RedirectResponse
    {
        if ($request->getMethod() === 'POST') {
            $this->converterWorker->convert(
                $request->get('converter'),
                $request->files->get('fileToUpload')
            );
        }

        return $this->redirectToRoute('converter_download_view');
    }

    /**
     * @Route("/converter/file/download", name="converter_download_file", methods={"POST"})
     */
    public function downloadFile(Request $request): Response
    {
        $url = $request->get('url');
        $response = new BinaryFileResponse($url);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            explode('/', $url)[4]
        );

        return $response;
    }

    /**
     * @Route("/converter/file/delete", name="converter_delete_file", methods={"POST"})
     */
    public function deleteFile(Request $request): Response
    {
        $this->convertedFileCrud->deleteFile($request->get('name'));

        return $this->redirectToRoute('converter_download_view');
    }
}
