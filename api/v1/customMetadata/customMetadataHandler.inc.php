<?php
/**
 * Handler to update the custom meta-data values provided in the publication stage. 
 */
import('lib.pkp.classes.handler.APIHandler');

class CustomMetadataHandler extends APIHandler
{
    public function __construct()
    {
        $this->_handlerPath = 'customMetadata';
        $roles = [ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR, ROLE_ID_AUTHOR];
        $this->_endpoints = array(
            'POST' => array(
                array(
                    'pattern' => $this->getEndpointPattern() . '/update/{submissionId}',
                    'handler' => array($this, 'updateCustomMetadata'),
                    'roles' => $roles
                ),                             
            ),          
        );
        parent::__construct();
    }

	function authorize($request, &$args, $roleAssignments) {
		import('lib.pkp.classes.security.authorization.ContextAccessPolicy');
		$this->addPolicy(new ContextAccessPolicy($request, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}



    public function updateCustomMetadata($slimRequest, $response, $args) {
        $request = APIHandler::getRequest();
        $requestParams = $slimRequest->getParsedBody();

        // Create a DAO for the Submissions
        $SubmissionDao = DAORegistry::getDAO('SubmissionDAO');

		// Get the review submission
        $submission = $SubmissionDao->getById($requestParams['submissionId']);    
            
        // Update the data object
        // Get the custom meta data fields for this submission
        $contextId = $submission->getData("contextId");
        $customMetadataDao = DAORegistry::getDAO('CustomMetadataDAO');
        $customFields = $customMetadataDao->getByContextId($contextId);			 
        while ($customField = $customFields->next()){
            // Get the setting_name of the field
            $customValueField = "customValue".$customField->getId();
            $submission->setData($customValueField, $requestParams[$customValueField]);            
        }
        $SubmissionDao->updateObject($submission);    

        // Log the metadata modification event.
        import('lib.pkp.classes.log.SubmissionLog');
        import('classes.log.SubmissionEventLogEntry');
        SubmissionLog::logEvent($request, $submission, SUBMISSION_LOG_METADATA_UPDATE, 'submission.event.general.metadataUpdated');        

        return $response->withJson(
            ['SubmissionId' => $requestParams['submissionId'],
            ], 200); 

    }

}
