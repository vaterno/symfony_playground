<?php

namespace App\Requests;

use DigitalRevolution\SymfonyRequestValidation\AbstractValidatedRequest;
use DigitalRevolution\SymfonyRequestValidation\Renderer\ViolationListRenderer;
use DigitalRevolution\SymfonyRequestValidation\Utility\PropertyPath;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class AbstractValidationRequest extends AbstractValidatedRequest
{
    protected function handleViolations(ConstraintViolationListInterface $violationList): ?Response
    {
        if (in_array('application/json', $this->getRequest()->getAcceptableContentTypes())) {
            $result = [
                'error' => true,
                'code' => Response::HTTP_BAD_REQUEST,
                'messages' => [],
            ];

            foreach ($violationList as $violation) {
                $propertyPath = str_replace('[request]', '', $violation->getPropertyPath());
                $propertyPath = implode('.', PropertyPath::toArray($propertyPath));

                $result['messages'][$propertyPath] = $violation->getMessage();
            }

            return (new JsonResponse($result, Response::HTTP_BAD_REQUEST));
        } else {
            throw new BadRequestException((new ViolationListRenderer($violationList))->render());
        }
    }
}
