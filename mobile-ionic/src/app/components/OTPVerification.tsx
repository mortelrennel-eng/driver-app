import { useState, useRef, useEffect } from "react";
import { motion } from "motion/react";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "./ui/card";
import { Button } from "./ui/button";
import { Car, Shield, ArrowLeft, RefreshCw } from "lucide-react";
import { toast } from "sonner";

interface OTPVerificationProps {
  email: string;
  onVerify: (otp: string) => Promise<void>;
  onResend?: () => Promise<void>;
  onBack: () => void;
  type: "login" | "signup";
}


export function OTPVerification({ email, onVerify, onResend, onBack, type }: OTPVerificationProps) {
  const [otp, setOtp] = useState(["", "", "", "", "", ""]);
  const [isVerifying, setIsVerifying] = useState(false);
  const [timer, setTimer] = useState(60);
  const [canResend, setCanResend] = useState(false);
  const inputRefs = useRef<(HTMLInputElement | null)[]>([]);

  useEffect(() => {
    // Focus first input on mount
    inputRefs.current[0]?.focus();
  }, []);

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
    // Only allow numbers
    if (value && !/^\d$/.test(value)) return;

    const newOtp = [...otp];
    newOtp[index] = value;
    setOtp(newOtp);

    // Auto-focus next input
    if (value && index < 5) {
      inputRefs.current[index + 1]?.focus();
    }

    // Auto-submit when all fields filled
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

  const handlePaste = (e: React.ClipboardEvent) => {
    e.preventDefault();
    const pastedData = e.clipboardData.getData("text").slice(0, 6);
    if (!/^\d+$/.test(pastedData)) return;

    const newOtp = pastedData.split("").concat(Array(6).fill("")).slice(0, 6);
    setOtp(newOtp);

    // Focus last filled input or verify if complete
    if (pastedData.length === 6) {
      setTimeout(() => handleVerify(pastedData), 100);
    } else {
      inputRefs.current[pastedData.length]?.focus();
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
      await onVerify(otpValue);
    } catch (error) {
      setOtp(["", "", "", "", "", ""]);
      inputRefs.current[0]?.focus();
    } finally {
      setIsVerifying(false);
    }
  };

  const handleResend = async () => {
    if (!canResend) return;
    
    if (onResend) {
      try {
        await onResend();
        setTimer(60);
        setCanResend(false);
        setOtp(["", "", "", "", "", ""]);
        inputRefs.current[0]?.focus();
      } catch (error) {
        // error handled by parent
      }
    } else {
      setTimer(60);
      setCanResend(false);
      setOtp(["", "", "", "", "", ""]);
      inputRefs.current[0]?.focus();
      toast.success("OTP sent to your email!");
    }
  };


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
        className="w-full max-w-md relative z-10"
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.5 }}
      >
        {/* Logo Section */}
        <div className="text-center mb-8">
          <motion.div
            className="inline-flex items-center justify-center mb-4 relative"
            initial={{ scale: 0 }}
            animate={{ scale: 1 }}
            transition={{ 
              type: "spring",
              stiffness: 200,
              damping: 15,
              delay: 0.2
            }}
          >
            <img src="/assets/logo.png" alt="Eurotaxi" className="h-20 object-contain filter drop-shadow-lg" />
          </motion.div>
          <motion.h1 
            className="text-3xl font-bold text-white mb-2"
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            transition={{ delay: 0.3 }}
          >
            Verify Your {type === "login" ? "Identity" : "Email"}
          </motion.h1>
          <motion.p 
            className="text-blue-200 text-sm"
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            transition={{ delay: 0.4 }}
          >
            Enter the 6-digit code sent to
          </motion.p>
          <motion.p 
            className="text-yellow-400 text-sm font-medium mt-1"
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            transition={{ delay: 0.5 }}
          >
            {email}
          </motion.p>
        </div>

        {/* OTP Card */}
        <motion.div
          initial={{ opacity: 0, scale: 0.95 }}
          animate={{ opacity: 1, scale: 1 }}
          transition={{ delay: 0.3 }}
        >
          <Card className="border-0 shadow-2xl backdrop-blur-sm bg-white/95">
            <CardHeader className="space-y-1 pb-4">
              <CardTitle className="text-2xl text-center">Enter OTP</CardTitle>
              <CardDescription className="text-center">
                We've sent a verification code to your email
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-6">
              {/* Demo Notice Removed */}


              {/* OTP Input */}
              <div className="flex justify-center gap-2" onPaste={handlePaste}>
                {otp.map((digit, index) => (
                  <motion.input
                    key={index}
                    ref={(el) => (inputRefs.current[index] = el)}
                    type="text"
                    inputMode="numeric"
                    maxLength={1}
                    value={digit}
                    onChange={(e) => handleChange(index, e.target.value)}
                    onKeyDown={(e) => handleKeyDown(index, e)}
                    className="w-12 h-14 text-center text-2xl font-bold border-2 rounded-lg focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 outline-none transition-all"
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: 0.7 + index * 0.05 }}
                    disabled={isVerifying}
                  />
                ))}
              </div>

              {/* Timer / Resend */}
              <div className="text-center">
                {!canResend ? (
                  <motion.p
                    className="text-sm text-gray-600"
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                  >
                    Resend code in{" "}
                    <span className="font-semibold text-yellow-600">
                      {timer}s
                    </span>
                  </motion.p>
                ) : (
                  <motion.button
                    onClick={handleResend}
                    className="text-sm text-yellow-600 hover:text-yellow-700 font-medium flex items-center justify-center gap-2 mx-auto hover:underline"
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    whileHover={{ scale: 1.05 }}
                    whileTap={{ scale: 0.95 }}
                  >
                    <RefreshCw className="h-4 w-4" />
                    Resend OTP
                  </motion.button>
                )}
              </div>

              {/* Verify Button */}
              <Button
                onClick={() => handleVerify()}
                className="w-full bg-yellow-400 hover:bg-yellow-500 text-gray-900 h-12"
                disabled={isVerifying || otp.join("").length !== 6}
              >
                {isVerifying ? (
                  <motion.div
                    className="flex items-center gap-2"
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                  >
                    <motion.div
                      className="h-4 w-4 border-2 border-gray-900 border-t-transparent rounded-full"
                      animate={{ rotate: 360 }}
                      transition={{
                        duration: 1,
                        repeat: Infinity,
                        ease: "linear",
                      }}
                    />
                    Verifying...
                  </motion.div>
                ) : (
                  "Verify OTP"
                )}
              </Button>

              {/* Back Button */}
              <Button
                onClick={onBack}
                variant="ghost"
                className="w-full"
                disabled={isVerifying}
              >
                <ArrowLeft className="h-4 w-4 mr-2" />
                Back to {type === "login" ? "Login" : "Sign Up"}
              </Button>

              {/* Security Notice */}
              <motion.div
                className="flex items-start gap-3 p-3 bg-gray-50 rounded-lg"
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                transition={{ delay: 0.8 }}
              >
                <Shield className="h-5 w-5 text-gray-600 mt-0.5 flex-shrink-0" />
                <div className="text-xs text-gray-600">
                  <p className="font-medium mb-1">Security Notice</p>
                  <p>
                    Never share your OTP with anyone. Eurotaxi staff will never ask
                    for your verification code.
                  </p>
                </div>
              </motion.div>
            </CardContent>
          </Card>
        </motion.div>

        {/* Footer */}
        <motion.div
          className="text-center mt-8 text-sm text-gray-400"
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          transition={{ delay: 0.9 }}
        >
          <p>© 2026 Eurotaxi. All rights reserved.</p>
        </motion.div>
      </motion.div>
    </div>
  );
}
