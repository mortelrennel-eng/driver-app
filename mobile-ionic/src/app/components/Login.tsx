import { useState } from "react";
import { useNavigate, Link } from "react-router-dom";
import { motion } from "motion/react";
import { Car, Eye, EyeOff, Mail, Lock, ArrowRight, Sparkles } from "lucide-react";
import { Button } from "./ui/button";
import { Input } from "./ui/input";
import { Label } from "./ui/label";
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "./ui/card";
import { toast } from "sonner";
import { useAuth } from "../context/AuthContext";
import { OTPVerification } from "./OTPVerification";

export function Login() {
  const navigate = useNavigate();
  const { login, verifyOTP, resendOTP } = useAuth();
  const [showPassword, setShowPassword] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [showOTP, setShowOTP] = useState(false);
  const [userToken, setUserToken] = useState("");
  const [errorMsg, setErrorMsg] = useState("");
  const [formData, setFormData] = useState({
    email: "",
    password: "",
  });

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg("");
    
    if (!formData.email || !formData.password) {
      setErrorMsg("Please fill in all fields.");
      return;
    }

    setIsLoading(true);

    try {
      const response = await login(formData.email, formData.password);
      
      if (response && response.mfa_required) {
        // New device detected — send OTP first, then show OTP screen
        try {
          await resendOTP(response.user_id, 'email');
          setUserToken(response.user_id);
          setShowOTP(true);
          toast.info("A new device was detected. A verification code was sent to your email.");
        } catch (otpError: any) {
          setErrorMsg(
            otpError.response?.data?.message || 
            otpError.message || 
            "Failed to send verification code. Please try again."
          );
        }
      } else {
        toast.success("Login successful! Welcome back!");
        navigate("/");
      }
    } catch (error: any) {
      const msg = error.response?.data?.message || error.message || "Login failed. Check your credentials.";
      setErrorMsg(msg);
      toast.error(msg);
    } finally {
      setIsLoading(false);
    }
  };

  const handleOTPVerify = async (otp: string) => {
    try {
      await verifyOTP(userToken, otp, 'mobile_app');
      toast.success("Device verified successfully!");
      navigate("/");
    } catch (error: any) {
      const msg = error.response?.data?.message || error.message || "Invalid OTP code.";
      toast.error(msg);
      throw error;
    }
  };

  const handleOTPResend = async () => {
    try {
      await resendOTP(userToken, 'email');
      toast.success("A new OTP has been sent to your email.");
    } catch (error: any) {
      const msg = error.response?.data?.message || error.message || "Failed to resend OTP.";
      toast.error(msg);
      throw error;
    }
  };


  if (showOTP) {
    return (
      <OTPVerification
        email={formData.email}
        onVerify={handleOTPVerify}
        onResend={handleOTPResend}
        onBack={() => setShowOTP(false)}
        type="login"
      />
    );
  }


  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#0a0f1e] via-[#0c1437] to-[#1e3a8a] p-4 relative overflow-hidden">
      {/* Animated Background Pattern */}
      <div className="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PHBhdGggZD0iTTM2IDE4YzAtMy4zMTQgMi42ODYtNiA2LTZzNiAyLjY4NiA2IDYtMi42ODYgNi02IDYtNi0yLjY4Ni02LTZ6TTEyIDQ4YzAtMy4zMTQgMi42ODYtNiA2LTZzNiAyLjY4NiA2IDYtMi42ODYgNi02IDYtNi0yLjY4Ni02LTZ6Ii8+PC9nPjwvZz48L3N2Zz4=')] opacity-20" />
      
      {/* Floating Taxi Icons */}
      <motion.div
        className="absolute top-20 left-10 text-yellow-400 opacity-20"
        animate={{
          y: [0, -20, 0],
          rotate: [0, 5, 0],
        }}
        transition={{
          duration: 4,
          repeat: Infinity,
          ease: "easeInOut",
        }}
      >
        <Car className="h-12 w-12" />
      </motion.div>
      
      <motion.div
        className="absolute bottom-20 right-10 text-yellow-400 opacity-20"
        animate={{
          y: [0, 20, 0],
          rotate: [0, -5, 0],
        }}
        transition={{
          duration: 5,
          repeat: Infinity,
          ease: "easeInOut",
        }}
      >
        <Car className="h-16 w-16" />
      </motion.div>

      <motion.div
        className="absolute top-1/2 left-1/4 text-yellow-400 opacity-10"
        animate={{
          x: [0, 30, 0],
          y: [0, -30, 0],
        }}
        transition={{
          duration: 6,
          repeat: Infinity,
          ease: "easeInOut",
        }}
      >
        <Car className="h-20 w-20" />
      </motion.div>
      
      <motion.div
        className="w-full max-w-md relative z-10"
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.5 }}
      >
        {/* Logo Section */}
        <div className="text-center mb-8">
          <motion.div
            className="inline-flex items-center justify-center mb-4 relative"
            initial={{ scale: 0, rotate: -180 }}
            animate={{ scale: 1, rotate: 0 }}
            transition={{ 
              type: "spring",
              stiffness: 200,
              damping: 15,
              delay: 0.2
            }}
          >
            <img src="/assets/logo.png" alt="Eurotaxi" className="h-24 object-contain filter drop-shadow-lg" />
            <motion.div
              className="absolute inset-0 rounded-full border-4 border-yellow-400/30"
              animate={{
                scale: [1, 1.2, 1],
                opacity: [0.5, 0, 0.5],
              }}
              transition={{
                duration: 3,
                repeat: Infinity,
                ease: "easeInOut",
              }}
            />
          </motion.div>
          <motion.h1 
            className="text-3xl font-extrabold text-white mb-2 flex items-center justify-center gap-2"
            initial={{ opacity: 0, y: -10 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.3 }}
          >
            <span className="text-yellow-400">Euro</span>taxi Admin
            <motion.div
              animate={{ rotate: [0, 15, 0] }}
              transition={{ duration: 2, repeat: Infinity }}
            >
              <Sparkles className="h-5 w-5 text-yellow-400" />
            </motion.div>
          </motion.h1>
          <motion.p 
            className="text-blue-200 text-sm font-medium tracking-widest uppercase"
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            transition={{ delay: 0.4 }}
          >
            Fleet Management System
          </motion.p>
        </div>

        {/* Login Card */}
        <motion.div
          initial={{ opacity: 0, scale: 0.95 }}
          animate={{ opacity: 1, scale: 1 }}
          transition={{ delay: 0.3 }}
        >
          <Card className="border-0 shadow-2xl backdrop-blur-sm bg-white/95">
            <CardHeader className="space-y-1">
              <CardTitle className="text-2xl text-center">Welcome back</CardTitle>
              <CardDescription className="text-center">
                Enter your credentials to access your account
              </CardDescription>
            </CardHeader>
            <CardContent>
              <form onSubmit={handleSubmit} className="space-y-4">
                {/* Error Message Box */}
                {errorMsg && (
                  <div className="bg-red-50 border border-red-200 rounded-xl p-3 flex items-start gap-2">
                    <span className="text-red-500 text-sm mt-0.5">⚠</span>
                    <p className="text-sm text-red-700 font-medium">{errorMsg}</p>
                  </div>
                )}
                <motion.div 
                  className="space-y-2"
                  initial={{ opacity: 0, x: -20 }}
                  animate={{ opacity: 1, x: 0 }}
                  transition={{ delay: 0.5 }}
                >
                  <Label htmlFor="login">Email or Phone Number</Label>
                  <div className="relative group">
                    <Mail className="absolute left-3 top-3 h-4 w-4 text-gray-400 group-focus-within:text-yellow-600 transition-colors" />
                    <Input
                      id="login"
                      type="text"
                      placeholder="admin@taxico.com or 09123456789"
                      value={formData.email}
                      onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                      className="pl-10 focus:ring-2 focus:ring-yellow-400 border-gray-200 transition-all"
                      required
                    />
                  </div>
                </motion.div>
                <motion.div 
                  className="space-y-2"
                  initial={{ opacity: 0, x: -20 }}
                  animate={{ opacity: 1, x: 0 }}
                  transition={{ delay: 0.6 }}
                >
                  <div className="flex items-center justify-between">
                    <Label htmlFor="password">Password</Label>
                    <Link
                      to="/forgot-password"
                      className="text-xs text-yellow-600 hover:text-yellow-700 hover:underline transition-colors"
                    >
                      Forgot password?
                    </Link>
                  </div>
                  <div className="relative group">
                    <Lock className="absolute left-3 top-3 h-4 w-4 text-gray-400 group-focus-within:text-yellow-600 transition-colors" />
                    <Input
                      id="password"
                      type={showPassword ? "text" : "password"}
                      placeholder="••••••••"
                      value={formData.password}
                      onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                      className="pl-10 pr-10 focus:ring-2 focus:ring-yellow-400 border-gray-200 transition-all"
                      required
                    />
                    <motion.button
                      type="button"
                      onClick={() => setShowPassword(!showPassword)}
                      className="absolute right-3 top-3 text-gray-400 hover:text-gray-600 transition-colors"
                      whileHover={{ scale: 1.1 }}
                      whileTap={{ scale: 0.9 }}
                    >
                      {showPassword ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                    </motion.button>
                  </div>
                </motion.div>

                <motion.div
                  initial={{ opacity: 0, y: 20 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ delay: 0.7 }}
                >
                  <Button
                    type="submit"
                    className="w-full bg-yellow-400 hover:bg-yellow-500 text-gray-900 h-11 relative overflow-hidden group"
                    disabled={isLoading}
                  >
                    <motion.div
                      className="absolute inset-0 bg-gradient-to-r from-yellow-300 to-yellow-500"
                      initial={{ x: "-100%" }}
                      whileHover={{ x: "100%" }}
                      transition={{ duration: 0.5 }}
                    />
                    <span className="relative flex items-center justify-center gap-2">
                      {isLoading ? (
                        <>
                          <motion.div
                            className="h-4 w-4 border-2 border-gray-900 border-t-transparent rounded-full"
                            animate={{ rotate: 360 }}
                            transition={{
                              duration: 1,
                              repeat: Infinity,
                              ease: "linear",
                            }}
                          />
                          Signing in...
                        </>
                      ) : (
                        <>
                          Sign in
                          <ArrowRight className="h-4 w-4 group-hover:translate-x-1 transition-transform" />
                        </>
                      )}
                    </span>
                  </Button>
                </motion.div>

              </form>
            </CardContent>
            <CardFooter className="flex flex-col space-y-4">
              <motion.div 
                className="text-sm text-center w-full"
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                transition={{ delay: 1.0 }}
              >
                <Link to="/about" className="text-gray-500 hover:text-gray-700 hover:underline transition-colors">
                  Learn more about TaxiCo
                </Link>
              </motion.div>
            </CardFooter>
          </Card>
        </motion.div>

        {/* Footer */}
        <motion.div
          className="text-center mt-8 text-sm text-gray-400"
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          transition={{ delay: 1.1 }}
        >
          <p>© 2026 TaxiCo. All rights reserved.</p>
        </motion.div>
      </motion.div>
    </div>
  );
}
