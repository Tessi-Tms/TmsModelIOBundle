<?php

/**
 * @author Jean-Philippe Chateau <jp.chateau@trepia.fr>
 */

namespace Tms\Bundle\ModelIOBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Form;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Abstract IO Controller
 */
abstract class AbstractIOController extends Controller
{
    /**
     * Export
     *
     * @param Object $entity        // The exported Entity
     * @param array  $entities      // Array of entities to export
     * @param string $modelName     // Name of the model
     * @param string $mode          // Mode
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function export($entity, array $entities, $modelName, $mode)
    {
        $content = $this->get('tms_model_io.manager.import_export_manager')->export($entities, $mode);
        $filename = sprintf('%s_%s_%s.json', str_replace(' ', '_', $entity), $modelName, $mode);

        $response = new Response($content);
        $response->headers->set('Content-type', 'application/json');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename=%s', $filename));

        return $response;
    }

    /**
     * Import
     *
     * @param Request $request    // The Request Object
     * @param Object  $entity     // The Object to import
     * @param string  $formAction // The form action route
     * @param string  $modelName  // The name of the model
     * @param string  $mode       // The mode
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function import(Request $request, $entity, $formAction, $modelName, $mode)
    {
        $form = $this->createForm('tms_model_io_import', $entity, array('label' => $this->get('translator')->trans('File')));

        if ($request->getMethod() === 'POST') {
            return $this->manageImportForm($request, $form, $entity, $modelName, $mode);
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
     * Manage the Import Form
     *
     * @param Request $request   // The Request Object
     * @param Form    $form      // The import form
     * @param Object  $entity    // The object to import
     * @param string  $modelName // The name of the model
     * @param string  $mode      // The mode
     */
    abstract protected function manageImportForm(Request $request, Form $form, $entity, $modelName, $mode);
}
