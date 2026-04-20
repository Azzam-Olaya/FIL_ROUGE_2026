<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navbar -->
    <nav class="bg-white shadow-sm border-b">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
          <div class="flex-shrink-0 flex items-center">
            <h1 class="text-2xl font-bold text-indigo-600 italic">MorLancer</h1>
          </div>
          <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
            <a href="#about" class="text-gray-900 px-1 pt-1 text-sm font-medium">À propos</a>
            <a href="#services" class="text-gray-500 hover:text-gray-700 px-1 pt-1 text-sm font-medium">Services</a>
            <a href="#contact" class="text-gray-500 hover:text-gray-700 px-1 pt-1 text-sm font-medium">Contact</a>
          </div>
          <div class="flex items-center space-x-4">
            <router-link to="/login" class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium">Connexion</router-link>
            <router-link to="/register" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700 transition">Inscription</router-link>
          </div>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <main>
      <div class="relative py-16 bg-gradient-to-br from-indigo-900 to-indigo-700 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
          <h2 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl">
            La plateforme n°1 pour le freelancing au Maroc
          </h2>
          <p class="mt-6 text-xl text-indigo-100 max-w-3xl mx-auto">
            Connectez-vous avec les meilleurs talents locaux ou trouvez des missions passionnantes dès aujourd'hui.
          </p>
          <div class="mt-10">
            <router-link to="/register" class="inline-block bg-white text-indigo-600 px-8 py-3 rounded-md font-bold text-lg hover:bg-gray-100 transition shadow-lg">
              Commencer votre expérience avec nous
            </router-link>
          </div>
        </div>
      </div>

      <!-- About Section -->
      <div id="about" class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
          <h3 class="text-3xl font-bold text-gray-900">À propos de MorLancer</h3>
          <p class="mt-4 text-lg text-gray-600 max-w-2xl mx-auto">
            MorLancer est une solution innovante dédiée à l'écosystème du freelancing marocain. Nous facilitons la mise en relation sécurisée entre clients et professionnels indépendants.
          </p>
        </div>
      </div>

      <!-- Services Section -->
      <div id="services" class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <h3 class="text-3xl font-bold text-gray-900 text-center mb-12">Nos Services</h3>
          <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
            <div v-for="service in services" :key="service.title" class="bg-white p-6 rounded-lg shadow-sm border hover:shadow-md transition">
              <div class="text-indigo-600 mb-4">
                <i :class="service.icon" class="text-3xl"></i>
              </div>
              <h4 class="text-xl font-semibold text-gray-900">{{ service.title }}</h4>
              <p class="mt-2 text-gray-600">{{ service.description }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Contact Section -->
      <div id="contact" class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <h3 class="text-3xl font-bold text-gray-900 text-center mb-8">Contactez-nous</h3>
          <div class="max-w-xl mx-auto bg-gray-50 p-8 rounded-lg border shadow-sm">
             <p class="text-center text-gray-600">Une question ? Un projet ? Notre équipe est là pour vous accompagner dans votre parcours sur MorLancer.</p>
             <div class="mt-6 flex justify-center">
                <button class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 transition">Envoyer un message</button>
             </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
      <div class="max-w-7xl mx-auto px-4 text-center">
        <p>&copy; 2026 MorLancer. Fièrement développé au Maroc.</p>
      </div>
    </footer>
  </div>
</template>

<script setup>
const services = [
  { title: 'Développement Web', description: 'Sites vitrines, e-commerce, applications sur mesure.', icon: 'fas fa-code' },
  { title: 'Design Graphique', description: 'Logos, branding, UI/UX design, supports de com.', icon: 'fas fa-paint-brush' },
  { title: 'Marketing Digital', description: 'SEO, réseaux sociaux, publicités payantes.', icon: 'fas fa-bullhorn' },
];
</script>
