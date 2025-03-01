<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\HistoriqueLogger;

class HistoriqueController extends AbstractController
{
    private string $logFile;
    private HistoriqueLogger $historiqueLogger;

    public function __construct(ContainerBagInterface $params, HistoriqueLogger $historiqueLogger)
    {
        $this->logFile = $params->get('kernel.logs_dir') . '/historique.log';
        $this->historiqueLogger = $historiqueLogger;
    }

    #[Route('/historique', name: 'historique_view')]
    public function viewHistorique(): Response
    {
        return $this->renderHistorique();
    }

    #[Route('/historique/post/{id}', name: 'historique_post')]
    public function historiquePost(int $id): Response
    {
        return $this->renderHistorique("Post ID $id");
    }

    #[Route('/historique/blog/{id}', name: 'historique_blog')]
    public function historiqueBlog(int $id): Response
    {
        return $this->renderHistorique("Blog ID $id");
    }

    private function renderHistorique(string $filter = null): Response
    {
        $logs = $this->historiqueLogger->getLogs();
        if ($filter !== null) {
            $logs = array_filter($logs, fn($log) => str_contains($log['message'], $filter));
        }

        // Remove IDs from log entries and handle unknown logs
        $logs = array_map(
            fn($log) => [
                'date' => $log['date'],
                'message' => preg_replace('/ with ID \d+/', '', $log['message']),
                'action' => $this->getActionFromMessage($log['message'])
            ],
            $logs
        );

        return $this->render('historique/index.html.twig', [
            'historique' => array_reverse($logs)
        ]);
    }

    private function getActionFromMessage(string $message): string
    {
        if (str_contains($message, 'created')) {
            return 'created';
        } elseif (str_contains($message, 'deleted')) {
            return 'deleted';
        } elseif (str_contains($message, 'edited')) {
            return 'edited';
        } else {
            return 'unknown';
        }
    }
}