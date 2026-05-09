<?php

namespace App\Http\Controllers;

use App\Services\GitHubService;
use Illuminate\Http\Request;

class GitHubIntegrationController extends Controller
{
    protected $githubService;

    public function __construct(GitHubService $githubService)
    {
        $this->githubService = $githubService;
    }

    /**
     * Display GitHub integration dashboard
     */
    public function index()
    {
        try {
            $stats = $this->githubService->getRepositoryStats();
            $commits = $this->githubService->getCommits('main', 10);
            $branches = $this->githubService->getBranches();
            $pulls = $this->githubService->getPullRequests('open');
            $issues = $this->githubService->getIssues('open');
            $contributors = $this->githubService->getContributors();

            return view('github.dashboard', compact(
                'stats',
                'commits',
                'branches',
                'pulls',
                'issues',
                'contributors'
            ));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load GitHub data: ' . $e->getMessage());
        }
    }

    /**
     * Get repository statistics (API)
     */
    public function getStats()
    {
        try {
            $stats = $this->githubService->getRepositoryStats();
            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get commits (API)
     */
    public function getCommits(Request $request)
    {
        try {
            $branch = $request->input('branch', 'main');
            $limit = $request->input('limit', 10);
            $commits = $this->githubService->getCommits($branch, $limit);

            return response()->json($commits);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get pull requests (API)
     */
    public function getPullRequests(Request $request)
    {
        try {
            $state = $request->input('state', 'open');
            $prs = $this->githubService->getPullRequests($state);

            return response()->json($prs);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get issues (API)
     */
    public function getIssues(Request $request)
    {
        try {
            $state = $request->input('state', 'open');
            $issues = $this->githubService->getIssues($state);

            return response()->json($issues);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Create a GitHub issue
     */
    public function createIssue(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'labels' => 'nullable|array',
        ]);

        try {
            $issue = $this->githubService->createIssue(
                $validated['title'],
                $validated['description'],
                $validated['labels'] ?? []
            );

            return response()->json([
                'message' => 'Issue created successfully',
                'issue' => $issue
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get repository contributors
     */
    public function getContributors()
    {
        try {
            $contributors = $this->githubService->getContributors();
            return response()->json($contributors);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get workflow status
     */
    public function getWorkflowStatus($workflowId)
    {
        try {
            $runs = $this->githubService->getWorkflowRuns($workflowId);
            return response()->json($runs);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Trigger GitHub Actions workflow
     */
    public function triggerWorkflow(Request $request)
    {
        $validated = $request->validate([
            'workflow_id' => 'required|string',
        ]);

        try {
            $triggered = $this->githubService->triggerWorkflow($validated['workflow_id']);

            if ($triggered) {
                return response()->json(['message' => 'Workflow triggered successfully']);
            } else {
                return response()->json(['error' => 'Failed to trigger workflow'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
