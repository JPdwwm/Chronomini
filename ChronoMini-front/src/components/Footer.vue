<script setup lang="ts">
import { ref } from 'vue';
import ContactModal from '@/components/ContactModal.vue';

// État pour contrôler l'affichage de la modal
const showContactModal = ref(false);

// Fonction pour ouvrir la modal de contact
const openContactModal = () => {
  showContactModal.value = true;
};

// Fonction pour fermer la modal de contact
const closeContactModal = () => {
  showContactModal.value = false;
};
</script>

<template>
  <footer class="footer">
    <div class="link">
      <button class="btn" @click="openContactModal">
        <span class="contact">Nous contacter</span>
      </button>
      <div class="legal-links">
        <router-link to="/legals" class="legal-link">Mentions légales</router-link>
        <span class="separator">|</span>
        <router-link to="/confid" class="legal-link">Confidentialité</router-link>
      </div>
    </div>
    <div class="logo-name">
      <router-link to="/" class="name-link" aria-label="Accueil ChronoMini">
        <img src="../assets/images/LogoChronoMini.webp" alt="ChronoMini Logo" class="logo">
        <p>ChronoMini</p>
      </router-link>
    </div>
    <div class="social">
      <a href="https://www.youtube.com/@ChronoMini" class="social-link" aria-label="Chaîne YouTube">
        <font-awesome-icon icon="fa-brands fa-youtube" class="yt"/>
      </a>
      <a href="https://www.instagram.com/chronomini/" class="social-link" aria-label="Page Instagram">
        <font-awesome-icon icon="fa-brands fa-instagram" class="insta"/>
      </a>
      <a href="https://linkedin.com/in/jean-philippe-gripacus" class="social-link" aria-label="Profil LinkedIn">
        <font-awesome-icon icon="fa-brands fa-linkedin-in" class="linkedin"/>
      </a>
    </div>
    
    <!-- Modal de contact -->
    <ContactModal 
      :show="showContactModal" 
      @close="closeContactModal"
    />
  </footer>
</template>

<style scoped>
.footer {
  height: 60px; /* Hauteur fixe conservée */
  background-color: #F7CD79;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 15px;
  box-sizing: border-box;
  box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.05);
}

.link {
  display: flex;
  flex-direction: column;
  height: 100%;
  justify-content: center;
}

.link a, .link span.contact {
  text-decoration: none;
  color: white;
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

.btn {
  width: 100%;
  margin: 2px 5px;
  padding: 3px 10px;
  background-color: #358E9D;
  color: white;
  font-weight: bold;
  font-size: 14px;
  border: none;
  border-radius: 15px;
  cursor: pointer;
  text-align: center;
  transition: background-color 0.2s, transform 0.2s;
}

.btn:hover {
  background-color: #286C72;
  transform: translateY(-1px);
}

/* Styles pour les liens légaux */
.legal-links {
  display: flex;
  justify-content: center;
  font-size: 12px;
  margin-top: 5px;
}

.legal-link {
  color: #358E9D;
  text-decoration: none;
  transition: color 0.2s;
}

.legal-link:hover {
  color: #286C72;
  text-decoration: underline;
}

.separator {
  margin: 0 5px;
  color: #358E9D;
}

.logo-name {
  display: flex;
  align-items: center;
  text-align: center;
  justify-content: center;
  gap: 8px;
  width: 50%;
  height: 100%;
}

.logo {
  height: 45px; 
  width: auto;
}

.logo-name p {
  font-family: 'Gloria Hallelujah', cursive, sans-serif;
  font-weight: bold;
  font-size: 26px; 
  color: #358E9D;
  margin: 0;
}

.social {
  display: flex;
  align-items: center;
  justify-content: space-around;
  width: 20%;
  height: 100%;
}

.social-link {
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.2s;
}

.social-link:hover {
  transform: translateY(-2px);
}

.yt, .insta, .linkedin {
  color: white;
  width: 25px; 
  height: 25px; 
  filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.1));
}

@media (max-width: 768px) {
  .btn {
    font-size: 12px;
    padding: 2px 8px;
  }
  
  .logo {
    height: 40px;
  }
  
  .logo-name p {
    font-size: 22px;
  }
  
  .yt, .insta, .linkedin {
    width: 22px;
    height: 22px;
  }
  
  .legal-links {
    font-size: 10px;
  }
}

@media (max-width: 425px) {
  .footer {
    padding: 0 10px;
  }
  
  .logo-name p {
    display: none; 
  }
  
  .social {
    width: 30%;
  }
  
  .legal-links {
    font-size: 9px;
  }
}
</style>