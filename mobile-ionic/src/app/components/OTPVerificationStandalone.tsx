import { useState, useRef, useEffect } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import { motion } from "motion/react";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "./ui/card";
import { Button } from "./ui/button";
import { Car, Shield, ArrowLeft, RefreshCw } from "lucide-react";
import { toast } from "sonner";
import axios from "axios";

const API_BASE_URL = "https://eurotaxisystem.site/api";

export function OTPVerificationStandalone() {
  const location = useLocation();
  const navigate = useNavigate();
  const { identifier, method } = location.state || {};

  const [otp, setOtp] = useState(["", "", "", "", "", ""]);
  const [isVerifying, setIsVerifying] = useState(false);
  const [timer, setTimer] = useState(60);
  const [canResend, setCanResend] = useState(false);
  const inputRefs = useRef<(HTMLInputElement | null)[]>([]);

  useEffect(() => {
    if (!identifier) {
      navigate("/forgot-password");
      return;
    }
    inputRefs.current[0]?.focus();
  }, [identifier, navigate]);

  useEffect(() => {
    if (timer > 0) {
      const interval = setInterval(() => {
        setTimer((prev) => prev - 1);
      }, 1000);
      return () => clearInterval(interval);
    } else {
      setCanResend(true);
    }
  }, [timer]);

  const handleChange = (index: number, value: string) => {
    if (value && !/^\d$/.test(value)) return;
    const newOtp = [...otp];
    newOtp[index] = value;
    setOtp(newOtp);
    if (value && index < 5) {
      inputRefs.current[index + 1]?.focus();
    }
    if (index === 5 && value) {
      const fullOtp = [...newOtp.slice(0, 5), value].join("");
      setTimeout(() => handleVerify(fullOtp), 100);
    }
  };

  const handleKeyDown = (index: number, e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.key === "Backspace" && !otp[index] && index > 0) {
      inputRefs.current[index - 1]?.focus();
    }
  };

  const handleVerify = async (otpToVerify?: string) => {
    const otpValue = otpToVerify || otp.join("");
    if (otpValue.length !== 6) {
      toast.error("Please enter complete OTP");
      return;
    }

    setIsVerifying(true);
    try {
      const response = await axios.post(`${API_BASE_URL}/verify-otp`, {
        identifier,
        otp: otpValue
      });

      if (response.data.success) {
        toast.success("OTP verified successfully!");
        navigate("/reset-password", { state: { identifier, otp: otpValue } });
      } else {
        toast.error(response.data.message || "Invalid OTP.");
      }
    } catch (error: any) {
      toast.error(error.response?.data?.message || "Verification failed.");
    } finally {
      setIsVerifying(false);
    }
  };

  const handleResend = async () => {
    if (!canResend) return;
    try {
      const response = await axios.post(`${API_BASE_URL}/forgot-password`, {
        identifier,
        method
      });
      if (response.data.success) {
        setTimer(60);
        setCanResend(false);
        setOtp(["", "", "", "", "", ""]);
        inputRefs.current[0]?.focus();
        toast.success("New OTP sent!");
      }
    } catch (error) {
      toast.error("Failed to resend OTP.");
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#0a0f1e] via-[#0c1437] to-[#1e3a8a] p-4 relative overflow-hidden">
      <div className="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PHBhdGggZD0iTTM2IDE4YzAtMy4zMTQgMi42ODYtNiA2LTZzNiAyLjY4NiA2IDYtMi42ODYgNi02IDYtNi0yLjY4Ni02LTZ6TTEyIDQ4YzAtMy4zMTQgMi42ODYtNiA2LTZzNiAyLjY4NiA2IDYtMi42ODYgNi02IDYtNi0yLjY4Ni02LTZ6Ii8+PC9nPjwvZz48L3N2Zz4=')] opacity-20" />
      
      <motion.div className="w-full max-w-md relative z-10" initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}>
        <div className="text-center mb-8">
          <img src="/assets/logo.png" alt="Eurotaxi" className="h-20 mx-auto mb-4" />
          <h1 className="text-3xl font-bold text-white mb-2">Verify OTP</h1>
          <p className="text-blue-200 text-sm">Enter the code sent to {identifier}</p>
        </div>

        <Card className="border-0 shadow-2xl bg-white/95">
          <CardHeader>
            <CardTitle className="text-2xl text-center">Security Check</CardTitle>
          </CardHeader>
          <CardContent className="space-y-6">
            <div className="flex justify-center gap-2">
              {otp.map((digit, index) => (
                <input
                  key={index}
                  ref={(el) => (inputRefs.current[index] = el)}
                  type="text"
                  inputMode="numeric"
                  maxLength={1}
                  value={digit}
                  onChange={(e) => handleChange(index, e.target.value)}
                  onKeyDown={(e) => handleKeyDown(index, e)}
                  className="w-12 h-14 text-center text-2xl font-bold border-2 rounded-lg focus:border-yellow-400 outline-none"
                  disabled={isVerifying}
                />
              ))}
            </div>

            <div className="text-center">
              {!canResend ? (
                <p className="text-sm text-gray-600">Resend in {timer}s</p>
              ) : (
                <button onClick={handleResend} className="text-sm text-yellow-600 font-medium flex items-center justify-center gap-2 mx-auto hover:underline">
                  <RefreshCw className="h-4 w-4" /> Resend OTP
                </button>
              )}
            </div>

            <Button onClick={() => handleVerify()} className="w-full bg-yellow-400 hover:bg-yellow-500 text-gray-900 h-12" disabled={isVerifying || otp.join("").length !== 6}>
              {isVerifying ? "Verifying..." : "Verify OTP"}
            </Button>

            <Button onClick={() => navigate("/forgot-password")} variant="ghost" className="w-full">
              <ArrowLeft className="h-4 w-4 mr-2" /> Back
            </Button>

            <div className="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
              <Shield className="h-5 w-5 text-gray-600 mt-0.5 flex-shrink-0" />
              <div className="text-xs text-gray-600">
                <p className="font-medium mb-1">Security Notice</p>
                <p>Never share your OTP with anyone. Eurotaxi staff will never ask for your code.</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </motion.div>
    </div>
  );
}
