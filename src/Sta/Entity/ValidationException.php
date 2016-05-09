<?php
/**
 * 
 *
 * @link      ${GITHUB_URL} Source code
 */
 
namespace Sta\Entity;

use ZF\ApiProblem\Exception\ProblemExceptionInterface;

class ValidationException extends \Sta\Exception implements ProblemExceptionInterface
{
    protected $code = 422;

    protected $validationMessages;
    
    public function __construct(array $validationMessages, $message = null)
    {
        $message = ($message ? $message : 'Failed Validation');

        if (class_exists('\PHPUnit_Framework_Assert', false)) {
            $message .= ". Messages:\n" . print_r($validationMessages, true);
        }

        parent::__construct($message, 422);
        
        $this->validationMessages = $validationMessages;
    }

    public function __toString()
    {
        $s = parent::__toString();
        $s .= "\n" . print_r($this->getAdditionalDetails(), true);
        return $s;
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
