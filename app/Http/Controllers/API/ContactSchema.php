<?php
namespace App\Http\Controllers\API;

use OpenApi\Annotations as OA;


/**
 * @OA\Schema(
 *     schema="ContactSchema",
 *     type="object",
 *     required={"name", "email", "subject", "message"},
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Name of the person contacting",
 *         example="John Doe"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         description="Email address",
 *         example="john.doe@example.com"
 *     ),
 *     @OA\Property(
 *         property="subject",
 *         type="string",
 *         description="Subject of the message",
 *         example="Inquiry about services"
 *     ),
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         description="Content of the message",
 *         example="I am interested in learning more about your services."
 *     )
 * )
 */

     class ContactSchema
{

}
