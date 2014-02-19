<?php

/**
 * @author Jean-Philippe Chateau <jp.chateau@trepia.fr>
 */

namespace Tms\Bundle\ModelIOBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Abstract IO Controller
 */
abstract class AbstractIOController extends Controller
{
    /**
     * Export
     *
     * @param array       $entities      // Array of entities to export
     * @param string      $mode          // Mode - The way data are exported
     * @param string|null $filename      // Name of the generated file
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function export(array $entities, $mode, $filename = null)
    {
        $content = $this->get('tms_model_io.manager.import_export_manager')->export($entities, $mode);
        $filename = sprintf('%s.json', $filename ? $filename : 'export');

        $response = new Response($content);
        $response->headers->set('Content-type', 'application/json');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename=%s', $filename));

        return $response;
    }

    /**
     * Import
     *
     * @param Request $request      // The Request Object
     * @param Object  $entity       // The Object to import
     * @param string  $formAction   // The form action route
     * @param string  $modelName    // The name of the model
     * @param string  $mode         // The mode
     * @param string  $redirectUrl  // The URL to redirect to after the processing of the form
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function import(Request $request, $entity, $formAction, $modelName, $mode, $redirectUrl)
    {
        $form = $this->createForm('tms_model_io_import', $entity);

        if ($request->getMethod() === 'POST') {
            $importExportManager = $this->get('tms_model_io.manager.import_export_manager');
            $form->handleRequest($request);

            if ($form->isValid()) {
                $file = $form['attachment']->getData();
                try {
                    $fileContent = $this->get('tms_model_io.handler.file_handler')->fileImport($file);
                    $entities = $importExportManager->import($fileContent, $modelName, $mode);
                } catch (\Exception $exception) {
                    $this->get('session')->getFlashBag()->add(
                        'error',
                        $this->get('translator')->trans($exception->getMessage()));

                    return $this->redirect($redirectUrl);
                }

                if ($form['remove-existing-entries']->getData()) {
                    $this->removeEntities($entity);
                }

                $this->importEntities($entities, $entity);

                $this->get('session')->getFlashBag()->add(
                    'success',
                    $this->get('translator')->trans('The file has been imported')
                );
            } else {
                $this->get('session')->getFlashBag()->add(
                    'error',
                    $this->get('translator')->trans('The file has not been imported')
                );
            }

            return $this->redirect($redirectUrl);
        }

        return $this->render(
            'TmsModelIOBundle:IO:import.html.twig',
            array(
                'entity' => $entity,
                'form'   => $form->createView(),
                'action' => $formAction
            )
        );
    }

    /**
     * Import Entities
     *
     * @param array       $entities   // Array of entities to import
     * @param Object|null $entity     // The entity to apply modifications
     */
    abstract protected function importEntities(array $entities, $entity = null);

    /**
     * Remove Entities
     *
     * @param Object|null $entity     // The entity to apply modifications
     */
    abstract protected function removeEntities($entity = null);
}
