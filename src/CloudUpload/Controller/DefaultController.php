<?php

namespace CloudUpload\Controller;

use CloudUpload\Manager\UploadManager;
use OpenCloud\OpenStack;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller{
    /**
     * @Route("/", name="homepage")
     *
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request){
        $client = new OpenStack('https://auth.cloud.ovh.net/v2.0/', array(
            'username' => 'XXXXXXXX',
            'password' => 'XXXXXXXX',
            'tenantId' => 'XXXXXXXX',
        ));

        $objectStoreService = $client->objectStoreService('swift', 'SBG1');

        $container = $objectStoreService->getContainer("XXXXXX");

        $manager = new UploadManager($container, array(
            "redirect" => "http://localhost:8000",
            "max_file_count" => "5",
            "max_file_size" => "1111111",
            "expires" => time() + 60*3
        ));

        $signature = $manager->generateSignature();
        $formData = $manager->generateForm($signature);

        return $this->render("CloudUpload:Main:index.html.twig", array(
            "title" => "Cloud upload",
            "url" => $manager->getActionUrl(),
            "formdata" => $formData
        ));
    }
}
