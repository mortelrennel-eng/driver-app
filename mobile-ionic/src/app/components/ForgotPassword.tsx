import { useState } from "react";
import { useNavigate, Link } from "react-router-dom";
import { motion } from "motion/react";
import { Mail, ArrowLeft, ArrowRight, ShieldCheck, Phone } from "lucide-react";
import { Button } from "./ui/button";
import { Input } from "./ui/input";
import { Label } from "./ui/label";
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "./ui/card";
import { toast } from "sonner";
import axios from "axios";

const API_BASE_URL = "https://eurotaxisystem.site/api";

export function ForgotPassword() {
  const navigate = useNavigate();
  const [identifier, setIdentifier] = useState("");
  const [method, setMethod] = useState<"email" | "phone">("email");
  const [isLoading, setIsLoading] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsLoading(true);

    try {
      const response = await axios.post(`${API_BASE_URL}/forgot-password`, {
        identifier,
        method
      });

      if (response.data.success) {
        toast.success(response.data.message || "OTP sent successfully!");
        // Navigate to OTP verification with the identifier
        navigate("/verify-otp", { state: { identifier, method } });
      } else {
        toast.error(response.data.message || "Failed to send OTP.");
      }
    } catch (error: any) {
      console.error("Forgot password error:", error);
      toast.error(error.response?.data?.message || "An error occurred. Please try again.");
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#0a0f1e] via-[#0c1437] to-[#1e3a8a] p-4 relative overflow-hidden">
      <div className="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PHBhdGggZD0iTTM2IDE4YzAtMy4zMTQgMi42ODYtNiA2LTZzNiAyLjY4NiA2IDYtMi42ODYgNi02IDYtNi0yLjY4Ni02LTZ6TTEyIDQ4YzAtMy4zMTQgMi42ODYtNiA2LTZzNiAyLjY4NiA2IDYtMi42ODYgNi02IDYtNi0yLjY4Ni02LTZ6Ii8+PC9nPjwvZz48L3N2Zz4=')] opacity-20" />
      
      <motion.div
        className="w-full max-w-md relative z-10"
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.5 }}
      >
        <div className="text-center mb-8">
          <motion.div
            className="inline-flex items-center justify-center mb-4 relative"
            initial={{ scale: 0, rotate: -180 }}
            animate={{ scale: 1, rotate: 0 }}
            transition={{ type: "spring", stiffness: 200, damping: 15, delay: 0.2 }}
          >
            <img src="/assets/logo.png" alt="Eurotaxi" className="h-20 object-contain filter drop-shadow-lg" />
          </motion.div>
          <motion.h1 className="text-2xl font-bold text-white mb-2">Forgot Password?</motion.h1>
          <motion.p className="text-blue-200 text-sm">We'll help you get back into your account</motion.p>
        </div>

        <motion.div initial={{ opacity: 0, scale: 0.95 }} animate={{ opacity: 1, scale: 1 }} transition={{ delay: 0.3 }}>
          <Card className="border-0 shadow-2xl backdrop-blur-sm bg-white/95">
            <CardHeader>
              <CardTitle className="text-xl text-center">Reset Password</CardTitle>
              <CardDescription className="text-center">
                Select your preferred reset method and enter your details.
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="flex bg-gray-100 p-1 rounded-lg mb-6">
                <button
                  className={`flex-1 py-2 text-sm font-medium rounded-md transition-all ${method === "email" ? "bg-white shadow-sm text-blue-600" : "text-gray-500 hover:text-gray-700"}`}
                  onClick={() => setMethod("email")}
                >
                  Email
                </button>
                <button
                  className={`flex-1 py-2 text-sm font-medium rounded-md transition-all ${method === "phone" ? "bg-white shadow-sm text-blue-600" : "text-gray-500 hover:text-gray-700"}`}
                  onClick={() => setMethod("phone")}
                >
                  Phone
                </button>
              </div>

              <form onSubmit={handleSubmit} className="space-y-4">
                <div className="space-y-2">
                  <Label htmlFor="identifier">
                    {method === "email" ? "Email Address" : "Phone Number"}
                  </Label>
                  <div className="relative group">
                    {method === "email" ? (
                      <Mail className="absolute left-3 top-3 h-4 w-4 text-gray-400 group-focus-within:text-yellow-600 transition-colors" />
                    ) : (
                      <Phone className="absolute left-3 top-3 h-4 w-4 text-gray-400 group-focus-within:text-yellow-600 transition-colors" />
                    )}
                    <Input
                      id="identifier"
                      type={method === "email" ? "email" : "tel"}
                      placeholder={method === "email" ? "your@email.com" : "09123456789"}
                      value={identifier}
                      onChange={(e) => setIdentifier(e.target.value)}
                      className="pl-10 focus:ring-2 focus:ring-yellow-400 border-gray-200 transition-all"
                      required
                    />
                  </div>
                </div>
                <Button
                  type="submit"
                  className="w-full bg-yellow-400 hover:bg-yellow-500 text-gray-900 h-11 relative overflow-hidden group"
                  disabled={isLoading}
                >
                  <span className="relative flex items-center justify-center gap-2">
                    {isLoading ? "Sending OTP..." : "Get Verification Code"}
                    {!isLoading && <ArrowRight className="h-4 w-4 group-hover:translate-x-1 transition-transform" />}
                  </span>
                </Button>
              </form>
            </CardContent>
            <CardFooter>
              <Link to="/login" className="w-full flex items-center justify-center gap-2 text-sm text-gray-600 hover:text-yellow-600 transition-colors">
                <ArrowLeft className="h-4 w-4" />
                Back to Login
              </Link>
            </CardFooter>
          </Card>
        </motion.div>

        <motion.div className="text-center mt-8 text-sm text-gray-400" initial={{ opacity: 0 }} animate={{ opacity: 1 }} transition={{ delay: 0.6 }}>
          <p>© 2026 Eurotaxi. All rights reserved.</p>
        </motion.div>
      </motion.div>
    </div>
  );
}
