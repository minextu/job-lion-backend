<?php namespace JobLion\CommentBundle\Controller;

use JobLion\AppBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use JobLion\ExperienceReportBundle\Entity\ExperienceReport;
use JobLion\CommentBundle\Entity\Comment;

/**
 * Comment api controller
 */
class CommentController extends AbstractController
{
    /**
     * @api        {post} /v1/experienceReports/:id/comments create
     * @apiName    createComment
     * @apiVersion 0.1.0
     * @apiGroup   Comment
     *
     * @apiParam {Number} id                Experience Report id
     * @apiParam {String} text              Report text
     *
     * @apiSuccess {Number} id              Id of the newly created comment
     *
     * @apiError        MissingValues       Some values weren't transmited
     * @apiError        InvalidId           Experience Report id does not exist
     *
     * @apiUse Login
     * @apiErrorExample Error-Response:
     * HTTP/1.1 400 Bad Request
     * {
     *    "error": "MissingValues"
     * }
     **/

    /**
     * Create a new Comment
     * @param  Request $request Info about this request
     * @return JsonResponse     Response in json format
     */
    public function create(Request $request, $experienceReportId) : JsonResponse
    {
        // check if logged in
        $error = $this->requireLogin($request);
        if ($error) {
            return $error;
        }

        $text = $request->get('text');

        // check for missing values
        if (!$text || !$experienceReportId) {
            return $this->app->json(
            ["error" => "MissingValues"],
            400
          );
        }

        // check if experience report is valid
        $experienceReport = $this->entityManager
                                 ->find(ExperienceReport::class, $experienceReportId);

        if (!$experienceReport) {
            return $this->app->json(
              ["error" => "InvalidId"],
              400
            );
        }

        // add the comment
        $comment = new Comment();
        $comment->setText($text)
                ->setExperienceReport($experienceReport)
                ->setUser($this->user);

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        // return success
        $response = new JsonResponse(["id" => $comment->getId()], 201);
        $url = $this->generateUrl(
          "experienceReport/$experienceReportId/comment",
          $comment->getId()
        );
        $response->headers->set('Location', $url);

        return $response;
    }

    /**
     * @api        {get} /v1/experienceReports/:id/comments list
     * @apiName    listComments
     * @apiVersion 0.1.0
     * @apiGroup   Comment
     *
     * @apiParam {Number} id                   Experience Report id
     * @apiParam {Number} [offset=0]           Number of entries to skip
     * @apiParam {Number} [limit=0]            Number of entries to show (0 for all)
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         [
     *             {
     *               "id": Number,
     *               "text": String,
     *               "user": {
     *                 "id": Number,
     *                 "avatar": String,
     *                 "firstName": String,
     *                 "lastName": String
     *               },
     *               "created": String
     *             }
     *         ]
     */

    /**
     * List all comments for this experience report
     * @param  Request $request Info about this request
     * @return JsonResponse     Response in json format
     */
    public function list(Request $request, $experienceReportId) : JsonResponse
    {
        $offset = $request->get('offset');
        $limit = $request->get('limit');

        // get all comments

        $comments = $this->entityManager
                                ->getRepository(Comment::class)
                                ->findByExperienceReport($experienceReportId, $offset, $limit);

        // get info array
        array_walk($comments, function (&$value, &$key) use ($request) {
            $value = $value->toArray($this->isAdmin($request));
        });

        // return all categories
        return $this->app->json(
          $comments,
          200
        );
    }
}
