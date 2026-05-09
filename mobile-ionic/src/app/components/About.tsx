import { Link } from "react-router-dom";
import { 
  Car, 
  TrendingUp, 
  Shield, 
  Users, 
  BarChart3, 
  Wrench, 
  DollarSign,
  Award,
  ArrowRight,
  CheckCircle2,
  Smartphone,
  Globe
} from "lucide-react";
import { Button } from "./ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "./ui/card";
import { ImageWithFallback } from "./figma/ImageWithFallback";

export function About() {
  const features = [
    {
      icon: Car,
      title: "Unit Management",
      description: "Comprehensive fleet management with real-time ROI tracking, acquisition history, and performance metrics for all taxi units."
    },
    {
      icon: DollarSign,
      title: "Boundary Management",
      description: "Automated boundary calculation with rules engine for new and old units, ensuring fair and transparent revenue sharing."
    },
    {
      icon: Wrench,
      title: "Maintenance & Parts",
      description: "Complete maintenance tracking system with mechanic assignments, parts inventory, and service history for optimal fleet health."
    },
    {
      icon: Users,
      title: "Driver Management",
      description: "Manage driver profiles, assignments (1-2 drivers per unit), documents, and performance tracking all in one place."
    },
    {
      icon: Award,
      title: "Driver Behavior",
      description: "Comprehensive scoring system that evaluates driver performance, compliance, and customer satisfaction metrics."
    },
    {
      icon: BarChart3,
      title: "Analytics & Insights",
      description: "Descriptive analytics that answer WHY questions, providing deep insights into operations, costs, and revenue trends."
    }
  ];

  const highlights = [
    "Two-sided system: Office/Admin portal and Owner monitoring dashboard",
    "Backend-first architecture for seamless multi-platform support",
    "Real-time data synchronization across all devices",
    "Complete business logic based on comprehensive taxi operations blueprint",
    "Role-based access control for security and data privacy",
    "Mobile-ready design for on-the-go management"
  ];

  const stats = [
    { label: "Modules", value: "8+" },
    { label: "Real-time Tracking", value: "24/7" },
    { label: "User Roles", value: "Multiple" },
    { label: "Analytics Reports", value: "30+" }
  ];

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-gradient-to-r from-gray-900 via-gray-800 to-yellow-900 text-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          <div className="flex items-center justify-between">
            <div className="flex items-center space-x-3">
              <div className="flex items-center justify-center w-12 h-12 bg-yellow-400 rounded-lg">
                <Car className="h-7 w-7 text-gray-900" />
              </div>
              <div>
                <h1 className="text-2xl">TaxiCo Admin</h1>
                <p className="text-sm text-gray-300">Taxi Company Management System</p>
              </div>
            </div>
            <div className="flex items-center space-x-3">
              <Link to="/login">
                <Button variant="ghost" className="text-white hover:bg-white/10">
                  Sign in
                </Button>
              </Link>
              <Link to="/signup">
                <Button className="bg-yellow-400 hover:bg-yellow-500 text-gray-900">
                  Get Started
                </Button>
              </Link>
            </div>
          </div>
        </div>
      </header>

      {/* Hero Section */}
      <section className="relative bg-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
              <div className="inline-block px-4 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm mb-4">
                Complete Taxi Management Solution
              </div>
              <h2 className="text-4xl lg:text-5xl mb-6">
                Streamline Your Taxi Operations with{" "}
                <span className="text-yellow-600">Modern Technology</span>
              </h2>
              <p className="text-xl text-gray-600 mb-8">
                A comprehensive, backend-first management system designed specifically for taxi companies. 
                Track units, manage drivers, monitor boundaries, and gain insights—all from a single platform.
              </p>
              <div className="flex flex-wrap gap-4">
                <Link to="/signup">
                  <Button size="lg" className="bg-yellow-400 hover:bg-yellow-500 text-gray-900">
                    Start Free Trial
                    <ArrowRight className="ml-2 h-5 w-5" />
                  </Button>
                </Link>
                <Link to="/login">
                  <Button size="lg" variant="outline">
                    Sign In
                  </Button>
                </Link>
              </div>
            </div>
            <div className="relative">
              <div className="aspect-[4/3] rounded-2xl overflow-hidden shadow-2xl">
                <ImageWithFallback
                  src="https://images.unsplash.com/photo-1767845934032-ec0e326f3a49?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHx5ZWxsb3clMjB0YXhpJTIwZmxlZXQlMjBtb2Rlcm4lMjBjaXR5fGVufDF8fHx8MTc3MDQ4NzU1Nnww&ixlib=rb-4.1.0&q=80&w=1080"
                  alt="Modern taxi fleet"
                  className="w-full h-full object-cover"
                />
              </div>
              {/* Floating stats cards */}
              <div className="absolute -bottom-6 -left-6 bg-white rounded-lg shadow-xl p-4 border border-gray-100">
                <div className="flex items-center space-x-3">
                  <div className="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <TrendingUp className="h-6 w-6 text-green-600" />
                  </div>
                  <div>
                    <p className="text-2xl">98%</p>
                    <p className="text-xs text-gray-600">Uptime</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Stats Section */}
      <section className="bg-gradient-to-r from-gray-900 to-gray-800 text-white py-12">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-2 lg:grid-cols-4 gap-8">
            {stats.map((stat, index) => (
              <div key={index} className="text-center">
                <div className="text-4xl mb-2 text-yellow-400">{stat.value}</div>
                <div className="text-sm text-gray-300">{stat.label}</div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Features Section */}
      <section className="py-20 bg-gray-50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-16">
            <h2 className="text-3xl lg:text-4xl mb-4">
              Everything You Need to Manage Your Taxi Fleet
            </h2>
            <p className="text-xl text-gray-600 max-w-3xl mx-auto">
              Our comprehensive system covers all aspects of taxi company operations, 
              from unit management to driver behavior tracking.
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            {features.map((feature, index) => (
              <Card key={index} className="border-0 shadow-lg hover:shadow-xl transition-shadow">
                <CardHeader>
                  <div className="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mb-4">
                    <feature.icon className="h-6 w-6 text-yellow-600" />
                  </div>
                  <CardTitle>{feature.title}</CardTitle>
                </CardHeader>
                <CardContent>
                  <CardDescription className="text-base">
                    {feature.description}
                  </CardDescription>
                </CardContent>
              </Card>
            ))}
          </div>
        </div>
      </section>

      {/* Technology Section */}
      <section className="py-20 bg-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div className="order-2 lg:order-1">
              <div className="aspect-[4/3] rounded-2xl overflow-hidden shadow-2xl">
                <ImageWithFallback
                  src="https://images.unsplash.com/photo-1657486232260-8be85026c8e0?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxtb2Rlcm4lMjB0YXhpJTIwbWFuYWdlbWVudCUyMG9mZmljZSUyMHRlY2hub2xvZ3l8ZW58MXx8fHwxNzcwNDg3NTU2fDA&ixlib=rb-4.1.0&q=80&w=1080"
                  alt="Modern management technology"
                  className="w-full h-full object-cover"
                />
              </div>
            </div>
            <div className="order-1 lg:order-2">
              <div className="inline-block px-4 py-1 bg-blue-100 text-blue-800 rounded-full text-sm mb-4">
                Backend-First Architecture
              </div>
              <h2 className="text-3xl lg:text-4xl mb-6">
                Built for Scale and Flexibility
              </h2>
              <p className="text-lg text-gray-600 mb-6">
                Our backend-first approach ensures that your data is always synchronized across all platforms. 
                Whether you're using the web admin portal or the mobile owner app, you'll have access to 
                real-time information.
              </p>
              <div className="space-y-3 mb-8">
                {highlights.map((highlight, index) => (
                  <div key={index} className="flex items-start space-x-3">
                    <CheckCircle2 className="h-6 w-6 text-green-600 flex-shrink-0 mt-0.5" />
                    <p className="text-gray-700">{highlight}</p>
                  </div>
                ))}
              </div>
              <div className="flex items-center space-x-8">
                <div className="flex items-center space-x-2 text-gray-600">
                  <Globe className="h-5 w-5" />
                  <span>Web Portal</span>
                </div>
                <div className="flex items-center space-x-2 text-gray-600">
                  <Smartphone className="h-5 w-5" />
                  <span>Mobile App</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Two-Sided System Section */}
      <section className="py-20 bg-gray-50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-16">
            <h2 className="text-3xl lg:text-4xl mb-4">
              Two-Sided System for Complete Control
            </h2>
            <p className="text-xl text-gray-600 max-w-3xl mx-auto">
              Separate interfaces designed for different roles, all connected to the same powerful backend.
            </p>
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <Card className="border-2 border-yellow-400 shadow-xl">
              <CardHeader>
                <div className="w-12 h-12 bg-yellow-400 rounded-lg flex items-center justify-center mb-4">
                  <Users className="h-6 w-6 text-gray-900" />
                </div>
                <CardTitle className="text-2xl">Office/Admin Portal</CardTitle>
                <CardDescription className="text-base">
                  Complete daily operations management
                </CardDescription>
              </CardHeader>
              <CardContent>
                <ul className="space-y-3">
                  <li className="flex items-start space-x-2">
                    <CheckCircle2 className="h-5 w-5 text-green-600 flex-shrink-0 mt-0.5" />
                    <span>Manage units, boundaries, and maintenance</span>
                  </li>
                  <li className="flex items-start space-x-2">
                    <CheckCircle2 className="h-5 w-5 text-green-600 flex-shrink-0 mt-0.5" />
                    <span>Track driver assignments and behavior</span>
                  </li>
                  <li className="flex items-start space-x-2">
                    <CheckCircle2 className="h-5 w-5 text-green-600 flex-shrink-0 mt-0.5" />
                    <span>Monitor office expenses and costs</span>
                  </li>
                  <li className="flex items-start space-x-2">
                    <CheckCircle2 className="h-5 w-5 text-green-600 flex-shrink-0 mt-0.5" />
                    <span>Generate comprehensive reports</span>
                  </li>
                </ul>
              </CardContent>
            </Card>

            <Card className="border-2 border-blue-400 shadow-xl">
              <CardHeader>
                <div className="w-12 h-12 bg-blue-400 rounded-lg flex items-center justify-center mb-4">
                  <Shield className="h-6 w-6 text-white" />
                </div>
                <CardTitle className="text-2xl">Owner Dashboard</CardTitle>
                <CardDescription className="text-base">
                  High-level monitoring and insights
                </CardDescription>
              </CardHeader>
              <CardContent>
                <ul className="space-y-3">
                  <li className="flex items-start space-x-2">
                    <CheckCircle2 className="h-5 w-5 text-green-600 flex-shrink-0 mt-0.5" />
                    <span>Real-time fleet performance overview</span>
                  </li>
                  <li className="flex items-start space-x-2">
                    <CheckCircle2 className="h-5 w-5 text-green-600 flex-shrink-0 mt-0.5" />
                    <span>ROI tracking and financial analytics</span>
                  </li>
                  <li className="flex items-start space-x-2">
                    <CheckCircle2 className="h-5 w-5 text-green-600 flex-shrink-0 mt-0.5" />
                    <span>Mobile app for on-the-go access</span>
                  </li>
                  <li className="flex items-start space-x-2">
                    <CheckCircle2 className="h-5 w-5 text-green-600 flex-shrink-0 mt-0.5" />
                    <span>Strategic decision-making insights</span>
                  </li>
                </ul>
              </CardContent>
            </Card>
          </div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="py-20 bg-gradient-to-r from-gray-900 via-gray-800 to-yellow-900 text-white">
        <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
          <h2 className="text-3xl lg:text-4xl mb-6">
            Ready to Transform Your Taxi Operations?
          </h2>
          <p className="text-xl text-gray-300 mb-8">
            Join modern taxi companies that trust our platform for complete fleet management.
          </p>
          <div className="flex flex-wrap justify-center gap-4">
            <Link to="/signup">
              <Button size="lg" className="bg-yellow-400 hover:bg-yellow-500 text-gray-900">
                Get Started Now
                <ArrowRight className="ml-2 h-5 w-5" />
              </Button>
            </Link>
            <Link to="/login">
              <Button size="lg" variant="outline" className="border-white text-white hover:bg-white/10">
                Sign In
              </Button>
            </Link>
          </div>
        </div>
      </section>

      {/* Footer */}
      <footer className="bg-gray-900 text-gray-400 py-12">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
            <div>
              <div className="flex items-center space-x-2 mb-4">
                <div className="w-8 h-8 bg-yellow-400 rounded-lg flex items-center justify-center">
                  <Car className="h-5 w-5 text-gray-900" />
                </div>
                <span className="text-white">TaxiCo Admin</span>
              </div>
              <p className="text-sm">
                Complete taxi company management system for modern operations.
              </p>
            </div>
            <div>
              <h3 className="text-white mb-4">Product</h3>
              <ul className="space-y-2 text-sm">
                <li><Link to="#" className="hover:text-white">Features</Link></li>
                <li><Link to="#" className="hover:text-white">Pricing</Link></li>
                <li><Link to="#" className="hover:text-white">Documentation</Link></li>
                <li><Link to="#" className="hover:text-white">API</Link></li>
              </ul>
            </div>
            <div>
              <h3 className="text-white mb-4">Company</h3>
              <ul className="space-y-2 text-sm">
                <li><Link to="/about" className="hover:text-white">About</Link></li>
                <li><Link to="#" className="hover:text-white">Blog</Link></li>
                <li><Link to="#" className="hover:text-white">Careers</Link></li>
                <li><Link to="#" className="hover:text-white">Contact</Link></li>
              </ul>
            </div>
            <div>
              <h3 className="text-white mb-4">Legal</h3>
              <ul className="space-y-2 text-sm">
                <li><Link to="#" className="hover:text-white">Privacy Policy</Link></li>
                <li><Link to="#" className="hover:text-white">Terms of Service</Link></li>
                <li><Link to="#" className="hover:text-white">Cookie Policy</Link></li>
                <li><Link to="#" className="hover:text-white">GDPR</Link></li>
              </ul>
            </div>
          </div>
          <div className="border-t border-gray-800 pt-8 text-sm text-center">
            <p>© 2026 TaxiCo. All rights reserved. Built with modern technology for taxi companies.</p>
          </div>
        </div>
      </footer>
    </div>
  );
}
