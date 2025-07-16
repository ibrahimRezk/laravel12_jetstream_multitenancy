<!-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alayen Teaching Hospital</title> -->
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.global.prod.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script> -->
<!-- <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0066cc',
                        secondary: '#00a86b',
                        accent: '#ff6b35'
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.6s ease-in-out',
                        'slide-up': 'slideUp 0.8s ease-out'
                    }
                }
            }
        }
    </script> -->

<script setup>
// const { createApp, ref, reactive, onMounted, onUnmounted } = Vue;

// createApp({
//     setup() {
const scrolled = ref(false);
const mobileMenuOpen = ref(false);

const form = reactive({
    name: "",
    email: "",
    phone: "",
    department: "",
    message: "",
});

const navItems = [
    { name: "Home", href: "#home" },
    { name: "Services", href: "#services" },
    { name: "Doctors", href: "#doctors" },
    { name: "Contact", href: "#contact" },
];

const stats = [
    { value: "50,000+", label: "Patients Treated Annually" },
    { value: "500+", label: "Specialized Doctors" },
    { value: "10,000+", label: "Successful Surgeries" },
    { value: "14+", label: "Years of Excellence" },
];

const services = [
    {
        id: 1,
        name: "Cardiology",
        description:
            "World-class cardiac care with advanced diagnostics and treatments for heart conditions.",
        icon: "M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z",
    },
    {
        id: 2,
        name: "Neurology",
        description:
            "Specialized care for disorders of the brain, spine, and nervous system.",
        icon: "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z",
    },
    {
        id: 3,
        name: "Orthopedics",
        description:
            "Comprehensive care for bone and joint conditions with minimally invasive procedures.",
        icon: "M13 10V3L4 14h7v7l9-11h-7z",
    },
    {
        id: 4,
        name: "Oncology",
        description:
            "Advanced cancer treatments with personalized care plans and support services.",
        icon: "M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z",
    },
    {
        id: 5,
        name: "Pediatrics",
        description:
            "Specialized healthcare for infants, children, and adolescents in a child-friendly environment.",
        icon: "M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z",
    },
    {
        id: 6,
        name: "Gastroenterology",
        description:
            "Diagnosis and treatment of digestive system disorders with advanced endoscopic procedures.",
        icon: "M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z",
    },
];

const doctors = [
    {
        id: 1,
        name: "Dr. Ahmed Hassan",
        specialty: "Cardiologist",
        experience:
            "Specialist in interventional cardiology with 15+ years of experience",
    },
    {
        id: 2,
        name: "Dr. Sarah Al-Rashid",
        specialty: "Neurologist",
        experience:
            "Leading expert in neurodegenerative disorders and stroke treatment",
    },
    {
        id: 3,
        name: "Dr. Omar Khalil",
        specialty: "Pediatrician",
        experience:
            "Specialized in developmental pediatrics and adolescent health care",
    },
    {
        id: 4,
        name: "Dr. Layla Mahmoud",
        specialty: "Orthopedic Surgeon",
        experience: "Expert in joint replacement surgery and sports medicine",
    },
];

const testimonials = [
    {
        id: 1,
        name: "Ali Mohammed",
        condition: "Heart Valve Disease",
        content:
            "After years of struggling with shortness of breath, I can finally play with my grandchildren again. The cardiac team at Alayen Hospital gave me a new lease on life. The care and attention I received was beyond my expectations.",
    },
    {
        id: 2,
        name: "Fatima Al-Zahra",
        condition: "Orthopedic Surgery",
        content:
            "The orthopedic team helped me regain full mobility after my accident. Their expertise and compassionate care made all the difference in my recovery journey.",
    },
    {
        id: 3,
        name: "Hassan Ibrahim",
        condition: "Cancer Treatment",
        content:
            "The oncology department provided not just medical treatment but emotional support throughout my cancer journey. I am grateful for their comprehensive care approach.",
    },
];

const handleScroll = () => {
    scrolled.value = window.scrollY > 50;
};

const scrollToSection = (href) => {
    const element = document.querySelector(href);
    if (element) {
        element.scrollIntoView({ behavior: "smooth" });
    }
};

const submitForm = () => {
    // Form submission logic
    alert("Thank you for your appointment request. We will contact you soon!");

    // Reset form
    Object.keys(form).forEach((key) => {
        form[key] = "";
    });
};

onMounted(() => {
    window.addEventListener("scroll", handleScroll);

    // Animate stats on scroll
    const observerOptions = {
        threshold: 0.5,
        rootMargin: "0px 0px -100px 0px",
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                const statElements = entry.target.querySelectorAll(
                    '[ref="statNumbers"]'
                );
                statElements.forEach((el, index) => {
                    const finalValue = stats[index].value;
                    const numericValue = parseInt(
                        finalValue.replace(/[^\d]/g, "")
                    );

                    let current = 0;
                    const increment = numericValue / 100;
                    const timer = setInterval(() => {
                        current += increment;
                        if (current >= numericValue) {
                            current = numericValue;
                            clearInterval(timer);
                        }
                        el.textContent = finalValue.replace(
                            /\d+/,
                            Math.floor(current).toLocaleString()
                        );
                    }, 20);
                });
            }
        });
    }, observerOptions);

    // Observe stats section
    setTimeout(() => {
        const statsSection = document.querySelector(
            ".grid.grid-cols-2.md\\:grid-cols-4"
        );
        if (statsSection) {
            observer.observe(statsSection);
        }
    }, 100);
});

onUnmounted(() => {
    window.removeEventListener("scroll", handleScroll);
});

// return {
//     scrolled,
//     mobileMenuOpen,
//     form,
//     navItems,
//     stats,
//     services,
//     doctors,
//     testimonials,
//     scrollToSection,
//     submitForm
// };
// }
// }).mount('#app');
</script>

<style>
@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}
@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.gradient-bg {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.card-hover {
    transition: all 0.3s ease;
}
.card-hover:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}
</style>
<!-- </head> -->
<!-- <body class="bg-zinc-50"> -->
<template>
    <div id="app">
        <!-- Navigation -->
        <nav
            class="bg-white shadow-lg fixed w-full top-0 z-50"
            :class="{ 'bg-white/95 backdrop-blur-xs': scrolled }"
        >
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center">
                        <div class="shrink-0">
                            <h1 class="text-2xl font-bold text-primary">
                                Alayen Hospital
                            </h1>
                        </div>
                    </div>
                    <div class="hidden md:block">
                        <div class="ml-10 flex items-baseline space-x-8">
                            <a
                                v-for="item in navItems"
                                :key="item.name"
                                :href="item.href"
                                class="text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium transition-colors duration-200"
                                @click="scrollToSection(item.href)"
                            >
                                {{ item.name }}
                            </a>
                        </div>
                    </div>
                    <div class="md:hidden">
                        <button
                            @click="mobileMenuOpen = !mobileMenuOpen"
                            class="text-gray-700"
                        >
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16"
                                />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            <div v-show="mobileMenuOpen" class="md:hidden bg-white border-t">
                <div class="px-2 pt-2 pb-3 space-y-1">
                    <a
                        v-for="item in navItems"
                        :key="item.name"
                        :href="item.href"
                        class="block px-3 py-2 text-gray-700 hover:text-primary"
                        @click="
                            scrollToSection(item.href);
                            mobileMenuOpen = false;
                        "
                    >
                        {{ item.name }}
                    </a>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section
            id="home"
            class="relative pt-16 gradient-bg text-white min-h-screen flex items-center"
        >
            <div class="absolute inset-0 bg-black/30"></div>
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
                <div class="text-center animate-fade-in">
                    <h1 class="text-5xl md:text-7xl font-bold mb-6">
                        Best Super Specialty Hospital & Teaching Centre in Iraq
                    </h1>
                    <p
                        class="text-xl md:text-2xl mb-8 max-w-4xl mx-auto opacity-90"
                    >
                        Established in 2010, providing the highest standards of
                        medical care while training the next generation of
                        healthcare professionals.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <button
                            @click="scrollToSection('#contact')"
                            class="bg-accent hover:bg-accent/90 text-white px-8 py-4 rounded-full text-lg font-semibold transition-all duration-300 transform hover:scale-105"
                        >
                            Book Appointment
                        </button>
                        <button
                            @click="scrollToSection('#services')"
                            class="border-2 border-white text-white hover:bg-white hover:text-gray-800 px-8 py-4 rounded-full text-lg font-semibold transition-all duration-300"
                        >
                            Our Services
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="py-16 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                    <div
                        v-for="stat in stats"
                        :key="stat.label"
                        class="text-center animate-slide-up"
                    >
                        <div
                            class="text-4xl md:text-5xl font-bold text-primary mb-2"
                        >
                            <span ref="statNumbers">{{ stat.value }}</span>
                        </div>
                        <div class="text-gray-600 font-medium">
                            {{ stat.label }}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Article -->
        <section class="py-16 bg-zinc-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="md:flex">
                        <div class="md:w-1/2 p-8 md:p-12">
                            <h2
                                class="text-3xl md:text-4xl font-bold text-gray-800 mb-4"
                            >
                                Understanding Heart Health: Prevention and Early
                                Detection
                            </h2>
                            <p class="text-gray-600 mb-6 text-lg">
                                Learn about the latest approaches to heart
                                disease prevention and how early detection can
                                save lives.
                            </p>
                            <button
                                class="bg-primary hover:bg-primary/90 text-white px-6 py-3 rounded-lg font-semibold transition-colors duration-200"
                            >
                                Read More
                            </button>
                        </div>
                        <div
                            class="md:w-1/2 bg-linear-to-br from-primary/10 to-secondary/10 flex items-center justify-center p-12"
                        >
                            <div
                                class="w-64 h-64 bg-primary/20 rounded-full flex items-center justify-center"
                            >
                                <svg
                                    class="w-32 h-32 text-primary"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section id="services" class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2
                        class="text-4xl md:text-5xl font-bold text-gray-800 mb-4"
                    >
                        Our Specialties
                    </h2>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                        Comprehensive healthcare services delivered by expert
                        specialists using cutting-edge technology
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div
                        v-for="service in services"
                        :key="service.id"
                        class="bg-white rounded-xl shadow-lg p-8 card-hover border border-gray-100"
                    >
                        <div
                            class="w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center mb-6"
                        >
                            <svg
                                class="w-8 h-8 text-primary"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path :d="service.icon" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-4">
                            {{ service.name }}
                        </h3>
                        <p class="text-gray-600 leading-relaxed">
                            {{ service.description }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Doctors Section -->
        <section id="doctors" class="py-20 bg-zinc-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2
                        class="text-4xl md:text-5xl font-bold text-gray-800 mb-4"
                    >
                        Meet Our Experts
                    </h2>
                    <p class="text-xl text-gray-600">
                        Our team of experienced medical professionals
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div
                        v-for="doctor in doctors"
                        :key="doctor.id"
                        class="bg-white rounded-xl shadow-lg overflow-hidden card-hover"
                    >
                        <div
                            class="h-64 bg-linear-to-br from-primary/20 to-secondary/20 flex items-center justify-center"
                        >
                            <div
                                class="w-32 h-32 bg-white rounded-full flex items-center justify-center"
                            >
                                <svg
                                    class="w-16 h-16 text-primary"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </div>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-2">
                                {{ doctor.name }}
                            </h3>
                            <p class="text-primary font-semibold mb-2">
                                {{ doctor.specialty }}
                            </p>
                            <p class="text-gray-600 text-sm">
                                {{ doctor.experience }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2
                        class="text-4xl md:text-5xl font-bold text-gray-800 mb-4"
                    >
                        Patient Stories
                    </h2>
                    <p class="text-xl text-gray-600">
                        Real stories from real patients about their journey to
                        recovery
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div
                        v-for="testimonial in testimonials"
                        :key="testimonial.id"
                        class="bg-zinc-50 rounded-xl p-8 card-hover"
                    >
                        <div class="flex mb-4">
                            <span
                                v-for="star in 5"
                                :key="star"
                                class="text-yellow-400 text-xl"
                                >★</span
                            >
                        </div>
                        <p class="text-gray-700 mb-6 italic">
                            "{{ testimonial.content }}"
                        </p>
                        <div class="border-t pt-4">
                            <p class="font-semibold text-gray-800">
                                {{ testimonial.name }}
                            </p>
                            <p class="text-primary text-sm">
                                {{ testimonial.condition }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Technology Section -->
        <section class="py-20 bg-primary text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-4xl md:text-5xl font-bold mb-4">
                        Advanced Technology
                    </h2>
                    <p class="text-xl opacity-90 max-w-3xl mx-auto">
                        At Alayen Teaching Hospital, we invest in the latest
                        medical technology to provide our patients with the most
                        advanced diagnostic and treatment options available.
                    </p>
                </div>

                <div class="grid md:grid-cols-2 gap-12 items-center">
                    <div>
                        <h3 class="text-3xl font-bold mb-6">
                            Robotic Surgical Technology
                        </h3>
                        <p class="text-lg opacity-90 mb-6">
                            Our hospital has invested in cutting-edge robotic
                            surgical technology that allows for more precise
                            procedures with faster recovery times for patients.
                        </p>
                        <ul class="space-y-3">
                            <li class="flex items-center">
                                <svg
                                    class="w-6 h-6 mr-3 text-accent"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                Minimally invasive procedures
                            </li>
                            <li class="flex items-center">
                                <svg
                                    class="w-6 h-6 mr-3 text-accent"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                Faster recovery times
                            </li>
                            <li class="flex items-center">
                                <svg
                                    class="w-6 h-6 mr-3 text-accent"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                Enhanced precision
                            </li>
                        </ul>
                    </div>
                    <div class="bg-white/10 rounded-2xl p-8 backdrop-blur-xs">
                        <div
                            class="w-full h-64 bg-white/20 rounded-xl flex items-center justify-center"
                        >
                            <svg
                                class="w-32 h-32 text-white/60"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"
                                />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="py-20 bg-zinc-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2
                        class="text-4xl md:text-5xl font-bold text-gray-800 mb-4"
                    >
                        Get In Touch
                    </h2>
                    <p class="text-xl text-gray-600">
                        Our team of medical experts is ready to provide you with
                        the best care possible
                    </p>
                </div>

                <div class="grid lg:grid-cols-2 gap-12">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-6">
                            Book an Appointment
                        </h3>
                        <form @submit.prevent="submitForm" class="space-y-6">
                            <div>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    placeholder="Full Name"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                />
                            </div>
                            <div>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    placeholder="Email Address"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                />
                            </div>
                            <div>
                                <input
                                    v-model="form.phone"
                                    type="tel"
                                    placeholder="Phone Number"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                />
                            </div>
                            <div>
                                <select
                                    v-model="form.department"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                >
                                    <option value="">Select Department</option>
                                    <option
                                        v-for="service in services"
                                        :key="service.id"
                                        :value="service.name"
                                    >
                                        {{ service.name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <textarea
                                    v-model="form.message"
                                    placeholder="Message"
                                    rows="4"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                ></textarea>
                            </div>
                            <button
                                type="submit"
                                class="w-full bg-primary hover:bg-primary/90 text-white py-4 rounded-lg font-semibold transition-colors duration-200"
                            >
                                Book Appointment
                            </button>
                        </form>
                    </div>

                    <div class="space-y-8">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800 mb-6">
                                Contact Information
                            </h3>
                            <div class="space-y-4">
                                <div class="flex items-center">
                                    <svg
                                        class="w-6 h-6 text-primary mr-4"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                    <span class="text-gray-700"
                                        >Baghdad, Iraq</span
                                    >
                                </div>
                                <div class="flex items-center">
                                    <svg
                                        class="w-6 h-6 text-primary mr-4"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"
                                        />
                                    </svg>
                                    <span class="text-gray-700"
                                        >+964 XXX XXX XXXX</span
                                    >
                                </div>
                                <div class="flex items-center">
                                    <svg
                                        class="w-6 h-6 text-primary mr-4"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"
                                        />
                                        <path
                                            d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"
                                        />
                                    </svg>
                                    <span class="text-gray-700"
                                        >info@alayenhospital.com</span
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-8 rounded-xl shadow-lg">
                            <h4 class="text-xl font-bold text-gray-800 mb-4">
                                Patient-Centered Care
                            </h4>
                            <p class="text-gray-600">
                                At Alayen Teaching Hospital, we believe in
                                putting our patients first. Our patient-centered
                                approach ensures that every aspect of your care
                                is tailored to your unique needs and
                                preferences.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-zinc-800 text-white py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid md:grid-cols-4 gap-8">
                    <div>
                        <h3 class="text-2xl font-bold mb-4">Alayen Hospital</h3>
                        <p class="text-gray-300 mb-4">
                            ADVANCED CARE • EXPERT TEACHING
                        </p>
                        <p class="text-gray-400 text-sm">
                            Combining cutting-edge medical care with
                            comprehensive training programs for the next
                            generation of healthcare professionals.
                        </p>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                        <ul class="space-y-2">
                            <li>
                                <a
                                    href="#home"
                                    class="text-gray-300 hover:text-white transition-colors"
                                    >Home</a
                                >
                            </li>
                            <li>
                                <a
                                    href="#services"
                                    class="text-gray-300 hover:text-white transition-colors"
                                    >Services</a
                                >
                            </li>
                            <li>
                                <a
                                    href="#doctors"
                                    class="text-gray-300 hover:text-white transition-colors"
                                    >Doctors</a
                                >
                            </li>
                            <li>
                                <a
                                    href="#contact"
                                    class="text-gray-300 hover:text-white transition-colors"
                                    >Contact</a
                                >
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold mb-4">Services</h4>
                        <ul class="space-y-2">
                            <li
                                v-for="service in services.slice(0, 4)"
                                :key="service.id"
                                class="text-gray-300 text-sm"
                            >
                                {{ service.name }}
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold mb-4">Contact</h4>
                        <div class="space-y-2 text-sm text-gray-300">
                            <p>Baghdad, Iraq</p>
                            <p>+964 XXX XXX XXXX</p>
                            <p>info@alayenhospital.com</p>
                        </div>
                    </div>
                </div>
                <div
                    class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400 text-sm"
                >
                    <p>
                        &copy; 2024 Alayen Teaching Hospital. All rights
                        reserved.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>

<!-- 
</body>
</html> -->
