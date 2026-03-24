<script setup lang="ts">
import { useAuthStore } from '@/stores/auth';
import { computed, ref } from 'vue';
import router from '@/router';

const authStore = useAuthStore();
const isAuthenticated = computed(() => authStore.isAuthenticated);
const isMobileMenuOpen = ref(false);

async function handleLogout() {
    await authStore.logout();
    router.push('/register-login');
}

function toggleMobileMenu() {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
}
</script>

<template>
  <header class="navbar">
    <div class="logo-name">
      <router-link to="/" class="name-link" aria-label="Accueil ChronoMini">
      <img src="../assets/images/LogoChronoMini.webp"  alt="ChronoMini Logo" class="logo">
        <p>ChronoMini</p>
      </router-link>
    </div>
    <button @click="toggleMobileMenu" class="burger-menu">
      <span :class="{ 'open': isMobileMenuOpen }"></span>
      <span :class="{ 'open': isMobileMenuOpen }"></span>
      <span :class="{ 'open': isMobileMenuOpen }"></span>
    </button>
    <nav class="navbar-links" :class="{ 'active': isMobileMenuOpen }">
      <router-link to="/" @click="isMobileMenuOpen = false">Accueil</router-link>
      <router-link to="/help" @click="isMobileMenuOpen = false">Aide</router-link>
      
      <div v-if="isAuthenticated" class="dropdown">
        <button class="dropdown-toggle">
          Enfants <span class="dropdown-arrow">▼</span>
        </button>
        <ul class="dropdown-menu">
          <li><router-link to="/kids" @click="isMobileMenuOpen = false">Gestion des enfants</router-link></li>
        </ul>
      </div>

      <div v-if="isAuthenticated" class="dropdown">
  <button class="dropdown-toggle">
    Mon compte <span class="dropdown-arrow">▼</span>
  </button>
  <ul class="dropdown-menu">
    <li><router-link to="/account" @click="isMobileMenuOpen = false">Mes informations</router-link></li>
    <li><router-link to="/link-user" @click="isMobileMenuOpen = false">Mes partenaires</router-link></li>
  </ul>
</div>
      
      <router-link 
        v-if="!isAuthenticated" 
        to="/register-login" 
        class="nav-button"
        @click="isMobileMenuOpen = false"
      >
        Se connecter
      </router-link>

      <div v-if="isAuthenticated" class="logout-container-mobile">
        <button @click="handleLogout" class="logout-button">Déconnexion</button>
      </div>
    </nav>

    <div v-if="isAuthenticated" class="logout-container-desktop">
      <button @click="handleLogout" class="logout-button">Déconnexion</button>
    </div>
  </header>
</template>

<style scoped>
body {
  margin: 0;
  padding: 0;
}

.navbar {
  position: fixed;
  top: 0;
  left: 0;
  display: flex;
  align-items: center;
  width: 100%; 
  height: 60px;
  background-color: #F7CD79;
  box-shadow: 0 4px 18px 0 rgba(0, 0, 0, 0.25);
  box-sizing: border-box; 
  font-size: 20px;
  font-family: 'Georgia',cursive, sans-serif;
  justify-content: space-between;
  padding: 0 2rem;
  z-index: 1000; /* Pour s'assurer que la navbar reste au-dessus des autres éléments */
}

.logo-name{
display: flex;
align-items: center;
text-align: center;
justify-content: space-between;
width: auto;
gap: 1rem;
}

.logo{
height: 60px;
}

.name-link {
  display: flex;
  align-items: center;
  gap: 8px;
  text-decoration: none;
}

.name-link p {
  font-family: 'Gloria Hallelujah', cursive, sans-serif;
  font-weight: bold;
  font-size: 26px;
  color: #358E9D;
  margin: 0;
}


.navbar-links,
.nav-auth {
  display: flex;
  align-items: center;
}

.navbar-links a,
.nav-auth a,
.dropdown-toggle {
  padding: 0.75rem 1.5rem;
  border-radius: 8px; /* Cohérent avec vos autres boutons */
  transition: all 0.3s ease;
  color: #358E9D;
  text-decoration: none;
}

.navbar-links a,
.nav-auth a,
.dropdown-menu li a {
font-weight: bold;
}

.navbar-links a:hover,
.nav-auth a:hover {
  background-color: rgba(53, 142, 157, 0.1);
}

.logout-button {
  background-color: #358E9D;
  color: white;
  border: none;
  cursor: pointer;
  border-radius: 8px;
  font-weight: bold;
  padding: 0.75rem 1.5rem;
  font-size: 1rem;
  transition: background-color 0.3s ease;
}

.logout-button:hover {
  background-color: #2b6f7d;
}

.dropdown {
position: relative;
display: inline-block;
}

.dropdown-toggle {
background: none;
border: none;
color: #358E9D;
font-weight: bold;
cursor: pointer;
display: flex;
align-items: center;
font-family: 'Georgia',cursive, sans-serif;
gap: 5px;
font-size: 20px; 
}

.dropdown-arrow {
font-size: 12px;
transition: transform 0.3s ease; /* Transition pour la rotation de la flèche */
}

.dropdown:hover .dropdown-arrow {
transform: rotate(180deg); /* Fait pivoter la flèche quand le menu est ouvert */
}

.dropdown-menu {
display: none;
position: absolute;
top: 100%; /* Juste en dessous du bouton */
left: 0;
background-color: white;
list-style: none;
padding: 10px 0;
box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
border-radius: 4px;
z-index: 2000;
width: 12vw;
border: 2px solid #c3c6ce; /* Même style de bordure que vos cartes */
background-color: #f5f5f5; /* Même fond que vos cartes */
border-radius: 8px;
width: auto;
min-width: 200px;
}

.dropdown-menu li {
transition: background-color 0.3s ease, color 0.3s ease;
}

.dropdown-menu li a {
font-size: 1rem;
padding: 0.75rem 1.5rem;
color: #358E9D;
text-decoration: none;
display: block;
white-space: nowrap; /* Empêche le texte de se couper sur plusieurs lignes */
}

.dropdown:hover .dropdown-menu {
display: block; 
}
/* Styles pour le burger menu */
.burger-menu {
  display: none;
  flex-direction: column;
  justify-content: space-around;
  width: 30px;
  height: 25px;
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 0;
  z-index: 10;
}

.burger-menu span {
  width: 30px;
  height: 3px;
  background: #358E9D;
  border-radius: 10px;
  transition: all 0.3s linear;
  position: relative;
  transform-origin: 1px;
}

/* Rotation des barres quand le menu est ouvert */
.burger-menu span.open:first-child {
  transform: rotate(45deg);
}

.burger-menu span.open:nth-child(2) {
  opacity: 0;
}

.burger-menu span.open:nth-child(3) {
  transform: rotate(-45deg);
}

.logout-container-mobile {
  display: none;
}

/* Media queries pour le responsive */
@media (max-width: 768px) {
  .burger-menu {
    display: flex;
  }

  .navbar-links {
    display: none;
    position: fixed;
    top: 60px;
    left: 0;
    right: 0;
    background-color: #F7CD79;
    flex-direction: column;
    align-items: center;
    padding: 2rem;
    gap: 1rem;
    z-index: 1000;
    transition: all 0.3s ease-in-out;
  }

  .navbar-links.active {
    display: flex;
  }

  .navbar-links a {
    width: 100%;
    text-align: center;
  }

  .dropdown {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  .dropdown-toggle {
    width: 100%;
    justify-content: center;
    padding: 0.75rem 1.5rem;
  }

  .dropdown-menu {
    position: static;
    width: 100%;
    display: none;
    margin-top: 0.5rem;
    border: none;  
    box-shadow: none;
    background-color: transparent; 
  }

  .dropdown-menu li {
    width: 100%;
  }

  .dropdown-menu li a {
    width: 100%;
    text-align: center;
  }

  .dropdown:hover .dropdown-menu {
    display: block;
  }

  .logout-container-desktop {
    display: none;
  }

  .logout-container-mobile {
    display: block;
    width: 100%;
    margin-top: 1rem;
  }

  .logout-container-mobile .logout-button {
    width: 100%;
  }

  .logo-name p {
    font-size: 24px;
  }

  .logo {
    height: 50px;
  }
}
</style>

