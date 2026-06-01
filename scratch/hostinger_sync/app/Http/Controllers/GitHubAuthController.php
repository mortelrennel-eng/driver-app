<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GitHubAuthController extends Controller
{
    /**
     * Redirect to GitHub OAuth
     */
    public function redirectToGitHub()
    {
        return Socialite::driver('github')->redirect();
    }

    /**
     * Handle GitHub callback
     */
    public function handleGitHubCallback()
    {
        try {
            $githubUser = Socialite::driver('github')->user();

            // Find or create user
            $user = User::firstOrCreate(
                ['email' => $githubUser->getEmail()],
                [
                    'full_name' => $githubUser->getName(),
                    'username' => $githubUser->getNickname(),
                    'password' => bcrypt(Str::random(16)),
                    'github_id' => $githubUser->getId(),
                    'github_token' => $githubUser->token,
                    'is_active' => true,
                    'role' => 'staff'
                ]
            );

            // Update GitHub info
            $user->update([
                'github_id' => $githubUser->getId(),
                'github_token' => $githubUser->token,
                'github_refresh_token' => $githubUser->refreshToken ?? null,
            ]);

            // Login user
            Auth::login($user, true);

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Successfully logged in with GitHub!');

        } catch (\Exception $e) {
            return redirect('/login')
                ->with('error', 'Failed to login with GitHub: ' . $e->getMessage());
        }
    }

    /**
     * Get user's GitHub repositories
     */
    public function getGitHubRepos()
    {
        $user = Auth::user();

        if (!$user->github_token) {
            return response()->json(['error' => 'GitHub token not found'], 401);
        }

        try {
            $repos = Socialite::driver('github')
                ->userFromToken($user->github_token)
                ->getRepositories();

            return response()->json($repos);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Sync repository with GitHub
     */
    public function syncWithGitHub()
    {
        $user = Auth::user();

        if (!$user->github_token) {
            return response()->json(['error' => 'GitHub token not found'], 401);
        }

        try {
            // Example: Create an issue on GitHub repository
            $client = new \GuzzleHttp\Client();
            $response = $client->get('https://api.github.com/user/repos', [
                'headers' => [
                    'Authorization' => 'token ' . $user->github_token,
                    'Accept' => 'application/vnd.github.v3+json',
                ],
            ]);

            $repos = json_decode($response->getBody());

            return response()->json([
                'message' => 'GitHub sync successful',
                'repos' => $repos
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
