import { useState } from "react";
import { useNavigate, Link } from "react-router-dom";
import { motion } from "motion/react";
import { Car, Eye, EyeOff, Mail, Lock, User, Phone, Sparkles, ArrowRight, CheckCircle2, XCircle } from "lucide-react";
import { Button } from "./ui/button";
import { Input } from "./ui/input";
import { Label } from "./ui/label";
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "./ui/card";
import { Checkbox } from "./ui/checkbox";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "./ui/select";
import { toast } from "sonner";
import axios from "axios";
import { OTPVerificationStandalone } from "./OTPVerificationStandalone";

const API_BASE_URL = "https://eurotaxisystem.site/api";

export function Signup() {
  const navigate = useNavigate();
  const [showPassword, setShowPassword] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [agreedToTerms, setAgreedToTerms] = useState(false);
  
  const [formData, setFormData] = useState({
    first_name: "",
    middle_name: "",
    last_name: "",
    suffix: "",
    phone_number: "",
    email: "",
    role: "",
    password: "",
    password_confirmation: "",
  });

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!agreedToTerms) {
      toast.error("Please agree to the terms and conditions");
      return;
    }

    if (formData.password !== formData.password_confirmation) {
      toast.error("Passwords do not match");
      return;
    }

    setIsLoading(true);

    try {
      const response = await axios.post(`${API_BASE_URL}/register`, formData);

      if (response.data.success) {
        toast.success(response.data.message || "Please verify your email.");
        // Redirect to a registration-specific OTP verification if needed, 
        // but for now let's assume the user needs to check email as per web logic.
        // On web, it shows an OTP entry. Let's redirect to our verify-otp page with a special state.
        navigate("/verify-otp", { state: { identifier: formData.email, type: "registration" } });
      }
    } catch (error: any) {
      console.error("Signup error:", error);
      toast.error(error.response?.data?.message || "Signup failed. Please check your details.");
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#0a0f1e] via-[#0c1437] to-[#1e3a8a] p-4 relative overflow-hidden">
      <div className="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PHBhdGggZD0iTTM2IDE4YzAtMy4zMTQgMi42ODYtNiA2LTZzNiAyLjY4NiA2IDYtMi42ODYgNi02IDYtNi0yLjY4Ni02LTZ6TTEyIDQ4YzAtMy4zMTQgMi42ODYtNiA2LTZzNiAyLjY4NiA2IDYtMi42ODYgNi02IDYtNi0yLjY4Ni02LTZ6Ii8+PC9nPjwvZz48L3N2Zz4=')] opacity-20" />
      
      <motion.div className="w-full max-w-3xl relative z-10 py-8" initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}>
        <div className="text-center mb-6">
          <img src="/assets/logo.png" alt="Eurotaxi" className="h-20 mx-auto mb-3" />
          <h1 className="text-2xl font-extrabold text-white mb-1">
            <span className="text-yellow-400">Euro</span>taxi Registration
          </h1>
          <p className="text-blue-200 text-sm tracking-widest uppercase font-medium">Fleet Management Access</p>
        </div>

        <Card className="border-0 shadow-2xl bg-white/95">
          <CardHeader className="pb-4">
            <CardTitle className="text-2xl text-center">Create Staff Account</CardTitle>
            <CardDescription className="text-center">Fill in your details for administrator approval.</CardDescription>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleSubmit} className="space-y-4">
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div className="space-y-2">
                  <Label>First Name *</Label>
                  <Input 
                    value={formData.first_name} 
                    onChange={(e) => setFormData({...formData, first_name: e.target.value})} 
                    placeholder="Juan" required 
                  />
                </div>
                <div className="space-y-2">
                  <Label>Middle Name</Label>
                  <Input 
                    value={formData.middle_name} 
                    onChange={(e) => setFormData({...formData, middle_name: e.target.value})} 
                    placeholder="Optional" 
                  />
                </div>
                <div className="space-y-2">
                  <Label>Last Name *</Label>
                  <Input 
                    value={formData.last_name} 
                    onChange={(e) => setFormData({...formData, last_name: e.target.value})} 
                    placeholder="Dela Cruz" required 
                  />
                </div>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label>Suffix</Label>
                  <Select value={formData.suffix} onValueChange={(v) => setFormData({...formData, suffix: v})}>
                    <SelectTrigger><SelectValue placeholder="N/A" /></SelectTrigger>
                    <SelectContent>
                      <SelectItem value=" ">N/A</SelectItem>
                      <SelectItem value="Jr.">Jr.</SelectItem>
                      <SelectItem value="Sr.">Sr.</SelectItem>
                      <SelectItem value="II">II</SelectItem>
                      <SelectItem value="III">III</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
                <div className="space-y-2">
                  <Label>Role *</Label>
                  <Select value={formData.role} onValueChange={(v) => setFormData({...formData, role: v})}>
                    <SelectTrigger><SelectValue placeholder="Select your role" /></SelectTrigger>
                    <SelectContent>
                      <SelectItem value="staff">Staff</SelectItem>
                      <SelectItem value="secretary">Secretary</SelectItem>
                      <SelectItem value="manager">Manager</SelectItem>
                      <SelectItem value="dispatcher">Dispatcher</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label>Phone Number (PH) *</Label>
                  <div className="relative">
                    <span className="absolute left-3 top-2.5 text-gray-500 text-sm">+63</span>
                    <Input 
                      className="pl-12"
                      placeholder="9123456789"
                      value={formData.phone_number}
                      onChange={(e) => setFormData({...formData, phone_number: e.target.value})}
                      required
                    />
                  </div>
                </div>
                <div className="space-y-2">
                  <Label>Email (Gmail only) *</Label>
                  <Input 
                    type="email"
                    placeholder="example@gmail.com"
                    value={formData.email}
                    onChange={(e) => setFormData({...formData, email: e.target.value})}
                    required
                  />
                </div>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label>Password *</Label>
                  <Input 
                    type="password"
                    placeholder="••••••••"
                    value={formData.password}
                    onChange={(e) => setFormData({...formData, password: e.target.value})}
                    required
                  />
                </div>
                <div className="space-y-2">
                  <Label>Confirm Password *</Label>
                  <Input 
                    type="password"
                    placeholder="••••••••"
                    value={formData.password_confirmation}
                    onChange={(e) => setFormData({...formData, password_confirmation: e.target.value})}
                    required
                  />
                </div>
              </div>

              <div className="flex items-start space-x-2 pt-2">
                <Checkbox id="terms" checked={agreedToTerms} onCheckedChange={(c) => setAgreedToTerms(c as boolean)} />
                <Label htmlFor="terms" className="text-xs text-gray-600 leading-tight cursor-pointer">
                  I agree to the Terms and Conditions and Privacy Policy.
                </Label>
              </div>

              <Button type="submit" className="w-full bg-yellow-400 hover:bg-yellow-500 text-gray-900 h-11" disabled={isLoading}>
                {isLoading ? "Processing..." : "Submit Registration"}
              </Button>
            </form>
          </CardContent>
          <CardFooter className="flex flex-col space-y-3 pt-2">
            <p className="text-sm text-center text-gray-600">
              Already have an account? <Link to="/login" className="text-yellow-600 hover:underline font-medium">Sign in</Link>
            </p>
          </CardFooter>
        </Card>
      </motion.div>
    </div>
  );
}
