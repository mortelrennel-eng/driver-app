import { useState } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import { motion } from "motion/react";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "./ui/card";
import { Button } from "./ui/button";
import { Lock, Eye, EyeOff, CheckCircle2 } from "lucide-react";
import { toast } from "sonner";
import { Input } from "./ui/input";
import { Label } from "./ui/label";
import axios from "axios";

const API_BASE_URL = "https://eurotaxisystem.site/api";

export function ResetPassword() {
  const location = useLocation();
  const navigate = useNavigate();
  const { identifier, otp } = location.state || {};

  const [password, setPassword] = useState("");
  const [passwordConfirmation, setPasswordConfirmation] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [isLoading, setIsLoading] = useState(false);

  if (!identifier || !otp) {
    navigate("/forgot-password");
    return null;
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (password !== passwordConfirmation) {
      toast.error("Passwords do not match!");
      return;
    }

    setIsLoading(true);
    try {
      const response = await axios.post(`${API_BASE_URL}/reset-password`, {
        identifier,
        otp,
        password,
        password_confirmation: passwordConfirmation
      });

      if (response.data.success) {
        toast.success("Password reset successfully!");
        navigate("/login");
      } else {
        toast.error(response.data.message || "Failed to reset password.");
      }
    } catch (error: any) {
      toast.error(error.response?.data?.message || "An error occurred.");
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#0a0f1e] via-[#0c1437] to-[#1e3a8a] p-4 relative overflow-hidden">
      <div className="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PHBhdGggZD0iTTM2IDE4YzAtMy4zMTQgMi42ODYtNiA2LTZzNiAyLjY4NiA2IDYtMi42ODYgNi02IDYtNi0yLjY4Ni02LTZ6TTEyIDQ4YzAtMy4zMTQgMi42ODYtNiA2LTZzNiAyLjY4NiA2IDYtMi42ODYgNi02IDYtNi0yLjY4Ni02LTZ6Ii8+PC9nPjwvZz48L3N2Zz4=')] opacity-20" />
      
      <motion.div className="w-full max-w-md relative z-10" initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}>
        <div className="text-center mb-8">
          <img src="/assets/logo.png" alt="Eurotaxi" className="h-20 mx-auto mb-4" />
          <h1 className="text-3xl font-bold text-white mb-2">New Password</h1>
          <p className="text-blue-200 text-sm">Create a secure password for your account</p>
        </div>

        <Card className="border-0 shadow-2xl bg-white/95">
          <CardHeader>
            <CardTitle className="text-2xl text-center">Secure Your Account</CardTitle>
            <CardDescription className="text-center">Enter your new password below.</CardDescription>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleSubmit} className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="password">New Password</Label>
                <div className="relative group">
                  <Lock className="absolute left-3 top-3 h-4 w-4 text-gray-400 group-focus-within:text-yellow-600 transition-colors" />
                  <Input
                    id="password"
                    type={showPassword ? "text" : "password"}
                    placeholder="••••••••"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    className="pl-10 pr-10 focus:ring-2 focus:ring-yellow-400 border-gray-200 transition-all"
                    required
                  />
                  <button
                    type="button"
                    onClick={() => setShowPassword(!showPassword)}
                    className="absolute right-3 top-3 text-gray-400 hover:text-gray-600"
                  >
                    {showPassword ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                  </button>
                </div>
              </div>

              <div className="space-y-2">
                <Label htmlFor="password_confirmation">Confirm Password</Label>
                <div className="relative group">
                  <CheckCircle2 className="absolute left-3 top-3 h-4 w-4 text-gray-400 group-focus-within:text-yellow-600 transition-colors" />
                  <Input
                    id="password_confirmation"
                    type={showPassword ? "text" : "password"}
                    placeholder="••••••••"
                    value={passwordConfirmation}
                    onChange={(e) => setPasswordConfirmation(e.target.value)}
                    className="pl-10 focus:ring-2 focus:ring-yellow-400 border-gray-200 transition-all"
                    required
                  />
                </div>
              </div>

              <div className="bg-blue-50 p-3 rounded-lg text-xs text-blue-700 space-y-1">
                <p className="font-bold">Password requirements:</p>
                <ul className="list-disc pl-4">
                  <li>At least 6 characters long</li>
                  <li>Include letters and numbers</li>
                </ul>
              </div>

              <Button type="submit" className="w-full bg-yellow-400 hover:bg-yellow-500 text-gray-900 h-12" disabled={isLoading}>
                {isLoading ? "Updating Password..." : "Reset Password"}
              </Button>
            </form>
          </CardContent>
        </Card>
      </motion.div>
    </div>
  );
}
