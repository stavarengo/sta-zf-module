<?php
/**
 * irmo Project ${PROJECT_URL}
 *
 * @link      ${GITHUB_URL} Source code
 */
 
namespace Sta\Entity;

use ZF\ApiProblem\Exception\ProblemExceptionInterface;

class ValidationException extends \Sta\Exception implements ProblemExceptionInterface
{
    protected $code = 422;

    protected $validationMessages;
    
    public function __construct(array $validationMessages)
    {
        $message = 'Failed Validation';
        parent::__construct($message, 422);
        
        $this->validationMessages = $validationMessages;
    }

    /**
     * @return null|array|\Traversable
     */
    public function getAdditionalDetails()
    {
        return  array('validation_messages' => $this->validationMessages);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return null;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return null;
    }
} 