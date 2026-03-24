<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require __DIR__ . '/../vendor/autoload.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

use App\Core\Router;

// Controllers
use App\Controllers\AuthController;
use App\Controllers\ProjectController;
use App\Controllers\ProjectMemberController;
use App\Controllers\ProjectInviteController;
use App\Controllers\BoardController;
use App\Controllers\TaskListController;
use App\Controllers\TaskController;
use App\Controllers\ReportController;
use App\Controllers\UserController;

$router = new Router();
// Auth
$router->add('POST', '/api/v1/auth/register', AuthController::class . '@register');
$router->add('POST', '/api/v1/auth/login', AuthController::class . '@login');
$router->add('POST', '/api/v1/auth/request-reset', AuthController::class . '@requestReset');
$router->add('POST', '/api/v1/auth/reset', AuthController::class . '@resetPassword');
$router->add('POST', '/api/v1/logout', AuthController::class . '@logout');
$router->add('GET', '/api/v1/me', AuthController::class . '@me');
// PROJECTS
$router->add('GET', '/api/v1/projects', ProjectController::class . '@index');
$router->add('POST', '/api/v1/projects', ProjectController::class . '@store');
$router->add('GET', '/api/v1/projects/{projectId}', ProjectController::class . '@show');
$router->add('PUT', '/api/v1/projects/{projectId}', ProjectController::class . '@update');
$router->add('DELETE', '/api/v1/projects/{projectId}', ProjectController::class . '@destroy');
// PROJECT MEMBERS
$router->add('GET', '/api/v1/projects/{projectId}/members', ProjectMemberController::class . '@index');
$router->add('PUT', '/api/v1/projects/{projectId}/members/{userId}', ProjectMemberController::class . '@update');
$router->add('DELETE', '/api/v1/projects/{projectId}/members/{userId}', ProjectMemberController::class . '@destroy');
// PROJECT INVITES
$router->add('GET', '/api/v1/projects/{projectId}/invites', ProjectInviteController::class . '@index');
$router->add('POST', '/api/v1/projects/{projectId}/invites', ProjectInviteController::class . '@store');
$router->add('POST', '/api/v1/invites/accept', ProjectInviteController::class . '@accept');
$router->add('POST', '/api/v1/projects/{projectId}/members', ProjectMemberController::class . '@store');
// BOARDS
$router->add('GET', '/api/v1/projects/{projectId}/boards', BoardController::class . '@getBoardsByProject');
$router->add('POST', '/api/v1/projects/{projectId}/boards', BoardController::class . '@create');
$router->add('GET', '/api/v1/boards/{boardId}', BoardController::class . '@getBoard');
$router->add('PUT', '/api/v1/boards/{boardId}', BoardController::class . '@update');
$router->add('DELETE', '/api/v1/boards/{boardId}', BoardController::class . '@destroy');

$router->add('GET', '/api/v1/boards/{boardId}/lists', BoardController::class . '@getLists');
// TASK LISTS
$router->add('POST', '/api/v1/boards/{boardId}/lists', TaskListController::class . '@create');
$router->add('PUT', '/api/v1/lists/{listId}', TaskListController::class . '@update');
$router->add('DELETE', '/api/v1/lists/{listId}', TaskListController::class . '@destroy');
$router->add('PUT', '/api/v1/lists/{listId}/sort', TaskListController::class . '@sort');
// TASKS
$router->add('POST', '/api/v1/lists/{listId}/tasks', TaskController::class . '@store');
$router->add('GET', '/api/v1/tasks/{taskId}', TaskController::class . '@show');
$router->add('PUT', '/api/v1/tasks/{taskId}', TaskController::class . '@update');
$router->add('DELETE', '/api/v1/tasks/{taskId}', TaskController::class . '@destroy');
$router->add('PUT', '/api/v1/tasks/{taskId}/move', TaskController::class . '@move');
$router->add('PUT', '/api/v1/tasks/{taskId}/sort', TaskController::class . '@sort');
// REPORTS
$router->add('GET', '/api/v1/reports/overview', ReportController::class . '@overview');
$router->add('GET', '/api/v1/reports/overdue', ReportController::class . '@overdue');
$router->add('GET', '/api/v1/reports/completed-per-user', ReportController::class . '@completedPerUser');

$router->add('GET', '/users', UserController::class . '@all');
$router->add('GET', '/api/v1/users', UserController::class . '@all');
$router->add('GET', '/api/v1/admin/users', UserController::class . '@index');
$router->add('PUT', '/api/v1/admin/users/{id}/role', UserController::class . '@updateRole');
$router->add('GET', '/api/v1/users', UserController::class . '@index');
$router->add('GET', '/api/v1/admin/projects', ProjectController::class . '@adminProjects');

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);