<?php

/**
 * @author Jean-Philippe Chateau <jp.chateau@trepia.fr>
 */

namespace Tms\Bundle\ModelIOBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * IO Controller
 */
class IOController extends Controller
{
    /**
     * Exports Participation
     *
     * @Route("export/participation/{id}/{type}.{format}", name="tms_operation_participation_export_type", requirements={"format"="json|xml|yml", "type"="participation|step|eligibility|benefit"})
     * @Route("export/participation/{id}.{format}", name="tms_operation_participation_export", requirements={"format"="json|xml|yml"})
     * @Method("GET")
     */
    public function exportAction(Participation $participation, $type = null, $format)
    {
        $importExportHandler = $this->get('tms_operation.participation.handler.import_export');

        $content = $importExportHandler->generateContent($participation, $type, $format);
        $filename = sprintf('%s.%s', $type ? $type : str_replace(' ', '_', $participation), $format);
        $mimeType = sprintf('application/%s', $format);

        $response = new Response($content);
        $response->headers->set('Content-type', $mimeType);
        $response->headers->set('Content-Disposition', sprintf('attachment; filename=%s', $filename));

        return $response;
    }

    /**
     * Imports Participation
     *
     * @Route("import/customer/{customer_id}/offer/{offer_id}", name="tms_operation_participation_import")
     * @Route("import/customer/{customer_id}/offer/{offer_id}/participation/{id}", name="tms_operation_participation_import_participation")
     * @ParamConverter("customer", class="TmsOperationBundle:Customer", options={"id"="customer_id"})
     * @ParamConverter("offer", class="TmsOperationBundle:Offer", options={"id"="offer_id"})
     * @Template("TmsWebAdminBundle:CustomerOfferParticipation:import.html.twig")
     */
    public function importAction(Request $request, Customer $customer, Offer $offer, Participation $participation = null)
    {
        $importExportHandler = $this->get('tms_operation.participation.handler.import_export');
        $form = $this->createForm('participation_import', $participation);

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $file = $form['attachment']->getData();
                $isProcessed = $importExportHandler->processFileImport($file, $offer, $participation);
                if ($isProcessed) {
                    $this->get('session')->getFlashBag()->add(
                            'success',
                            $this->get('translator')->trans('The file has been imported')
                    );
                } else {
                    $this->get('session')->getFlashBag()->add(
                            'error',
                            $this->get('translator')->trans('An error occured while importing the file')
                    );
                }
            } else {
                $this->get('session')->getFlashBag()->add(
                        'error',
                        $this->get('translator')->trans('The file has not been imported')
                );
            }

            return $this->redirect($this->generateUrl(
                    'tms_operation_customer_offer_participation',
                    array(
                            'customer_id' => $customer->getId(),
                            'offer_id' => $offer->getId()
                    )
            ));
        }

        return array(
                'entity'      => $participation,
                'customer_id' => $customer->getId(),
                'offer_id'    => $offer->getId(),
                'form'        => $form->createView()
        );
    }
}
