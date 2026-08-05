<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class GitHubService
{
    protected $client;
    protected $token;
    protected $baseUrl = 'https://api.github.com';
    protected $owner = 'Sony0012';
    protected $repo = 'eurotaxisystem';

    public function __construct()
    {
        $this->client = new Client();
        $this->token = config('services.github.token');
    }

    /**
     * Get default headers for API requests
     */
    private function getHeaders()
    {
        return [
            'Authorization' => 'token ' . $this->token,
            'Accept' => 'application/vnd.github.v3+json',
            'User-Agent' => 'EuroTaxi-System',
        ];
    }

    /**
     * Get repository information
     */
    public function getRepository()
    {
        return Cache::remember('github_repo_info', 3600, function () {
            $response = $this->client->get(
                "{$this->baseUrl}/repos/{$this->owner}/{$this->repo}",
                ['headers' => $this->getHeaders()]
            );

            return json_decode($response->getBody(), true);
        });
    }

    /**
     * Get repository branches
     */
    public function getBranches()
    {
        return Cache::remember('github_branches', 1800, function () {
            $response = $this->client->get(
                "{$this->baseUrl}/repos/{$this->owner}/{$this->repo}/branches",
                ['headers' => $this->getHeaders()]
            );

            return json_decode($response->getBody(), true);
        });
    }

    /**
     * Get latest commits
     */
    public function getCommits($branch = 'main', $limit = 10)
    {
        $response = $this->client->get(
            "{$this->baseUrl}/repos/{$this->owner}/{$this->repo}/commits",
            [
                'headers' => $this->getHeaders(),
                'query' => [
                    'sha' => $branch,
                    'per_page' => $limit,
                ]
            ]
        );

        return json_decode($response->getBody(), true);
    }

    /**
     * Get repository releases
     */
    public function getReleases()
    {
        return Cache::remember('github_releases', 3600, function () {
            $response = $this->client->get(
                "{$this->baseUrl}/repos/{$this->owner}/{$this->repo}/releases",
                ['headers' => $this->getHeaders()]
            );

            return json_decode($response->getBody(), true);
        });
    }

    /**
     * Get pull requests
     */
    public function getPullRequests($state = 'open')
    {
        $response = $this->client->get(
            "{$this->baseUrl}/repos/{$this->owner}/{$this->repo}/pulls",
            [
                'headers' => $this->getHeaders(),
                'query' => ['state' => $state]
            ]
        );

        return json_decode($response->getBody(), true);
    }

    /**
     * Get issues
     */
    public function getIssues($state = 'open')
    {
        $response = $this->client->get(
            "{$this->baseUrl}/repos/{$this->owner}/{$this->repo}/issues",
            [
                'headers' => $this->getHeaders(),
                'query' => ['state' => $state]
            ]
        );

        return json_decode($response->getBody(), true);
    }

    /**
     * Create an issue
     */
    public function createIssue($title, $description, $labels = [])
    {
        $response = $this->client->post(
            "{$this->baseUrl}/repos/{$this->owner}/{$this->repo}/issues",
            [
                'headers' => $this->getHeaders(),
                'json' => [
                    'title' => $title,
                    'body' => $description,
                    'labels' => $labels,
                ]
            ]
        );

        return json_decode($response->getBody(), true);
    }

    /**
     * Get file content from repository
     */
    public function getFileContent($filePath)
    {
        $response = $this->client->get(
            "{$this->baseUrl}/repos/{$this->owner}/{$this->repo}/contents/{$filePath}",
            ['headers' => $this->getHeaders()]
        );

        $data = json_decode($response->getBody(), true);
        return base64_decode($data['content']);
    }

    /**
     * Get repository statistics
     */
    public function getRepositoryStats()
    {
        return Cache::remember('github_repo_stats', 3600, function () {
            $repo = $this->getRepository();
            $commits = $this->getCommits();
            $pulls = $this->getPullRequests('all');
            $issues = $this->getIssues('all');

            return [
                'name' => $repo['name'],
                'description' => $repo['description'],
                'url' => $repo['html_url'],
                'stars' => $repo['stargazers_count'],
                'forks' => $repo['forks_count'],
                'watchers' => $repo['watchers_count'],
                'open_issues' => $repo['open_issues_count'],
                'language' => $repo['language'],
                'created_at' => $repo['created_at'],
                'updated_at' => $repo['updated_at'],
                'total_commits' => count($commits),
                'total_prs' => count($pulls),
                'total_issues' => count($issues),
            ];
        });
    }

    /**
     * Get contributor statistics
     */
    public function getContributors()
    {
        return Cache::remember('github_contributors', 3600, function () {
            $response = $this->client->get(
                "{$this->baseUrl}/repos/{$this->owner}/{$this->repo}/contributors",
                ['headers' => $this->getHeaders()]
            );

            return json_decode($response->getBody(), true);
        });
    }

    /**
     * Trigger GitHub Actions workflow
     */
    public function triggerWorkflow($workflowId)
    {
        $response = $this->client->post(
            "{$this->baseUrl}/repos/{$this->owner}/{$this->repo}/actions/workflows/{$workflowId}/dispatches",
            [
                'headers' => array_merge($this->getHeaders(), [
                    'Accept' => 'application/vnd.github.v3+json',
                ]),
                'json' => ['ref' => 'main']
            ]
        );

        return $response->getStatusCode() === 204;
    }

    /**
     * Get workflow runs
     */
    public function getWorkflowRuns($workflowId, $limit = 5)
    {
        $response = $this->client->get(
            "{$this->baseUrl}/repos/{$this->owner}/{$this->repo}/actions/workflows/{$workflowId}/runs",
            [
                'headers' => $this->getHeaders(),
                'query' => ['per_page' => $limit]
            ]
        );

        return json_decode($response->getBody(), true);
    }
}
