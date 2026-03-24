<script setup lang="ts">
</script>

<template>
  <div class="main-wrapper">
    <section class="hero-section">
      <div class="content-container">
        <div class="text-content">
          <h1>Optimisez et Simplifiez le Calcul des Heures de Garde</h1>
          <p class="subtitle">Simplifiez la gestion des heures de garde avec notre application intuitive. Gagnez du temps, évitez les erreurs et assurez une traçabilité parfaite de chaque journée.</p>
          <button class="primary-button" @click="$router.push('/help')">Suivez le guide</button>
        </div>
        <div class="image-content">
          <img src="../assets/images/HomePhoto.webp" alt="Enfants jouant avec le sourire aux lèvres" class="hero-image">
        </div>
      </div>
    </section>
    <div class="second-container">
      <div class="card-container">
          <article class="card-article">
            <div class="card-data">
              <h2 class="card-title">Gestion des heures</h2>
            </div>
            <img src="../assets/images/horloge.webp" alt="image d'une horloge" class="card-img">
            <img src="../assets/images/coloredBG.webp" alt="image d'un fond coloré" class="card-bg">
            <button class="card-btn" @click="$router.push({ path: '/help', hash: '#gestion-heures' })">
                En savoir plus
            </button>
          </article>

          <article class="card-article">
            <div class="card-data">
              <h2 class="card-title">Téléchargement de relevés</h2>
            </div>
            <img src="../assets/images/downloadImage.webp" alt="image d'un logo de téléchargement" class="card-img">
            <img src="../assets/images/coloredBG.webp" alt="image d'un fond coloré" class="card-bg">
            <button class="card-btn" @click="$router.push({ path: '/help', hash: '#telecharger-releves' })">
                En savoir plus
            </button>
          </article>

          <article class="card-article">
            <div class="card-data">
              <h2 class="card-title">Fiabilité et précision</h2>
            </div>
            <img src="../assets/images/dollarz.webp" alt="image de pirèces de monnaie formant le signe euro" class="card-img">
            <img src="../assets/images/coloredBG.webp" alt="image d'un fond coloré" class="card-bg">
            <button class="card-btn" @click="$router.push({ path: '/help', hash: '#fiabilite-precision' })">
                En savoir plus
            </button>
          </article>
      </div>
    </div>
    <div class="third-container">
      <div class="kids-images">
        <img src="../assets/images/EnfantCerceau.webp" alt="Petite fille jouant avec un cerceau" class="kid-hoop">
        <img src="../assets/images/PetiteFille.webp" alt="Petite fille heureuse" class="little-girl">
        <img src="../assets/images/Bebe.webp" alt="Bebe assis" class="baby">
        <div class="outer">
        <div class="ball">.</div>
        <div class="ball"></div>
      </div>
      </div>
    </div>

  </div>
</template>

<style scoped>
.main-wrapper {
  min-height: 90vh; 
  position: relative;
  overflow: visible; 
  font-family: Georgia, 'Times New Roman', Times, serif;
}

/* Nouvelle Hero Section */
.hero-section {
  min-height: 70vh;
  padding: 2rem;
  display: flex;
  align-items: center;
  background-color: #f5f5f5;
  overflow: hidden;
}

.content-container {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  gap: 4rem;
}

.text-content {
  flex: 1;
  animation: slideInLeftContainer 1.5s ease;
}

.text-content h1 {
  color: #78BB99;
  font-size: 2.5rem;
  margin-bottom: 1.5rem;
  line-height: 1.2;
}

.subtitle {
  color: #666;
  font-size: 1.2rem;
  margin-bottom: 2rem;
  line-height: 1.6;
}

.image-content {
  flex: 1;
  animation: slideInImg 1.5s ease;
}

.hero-image {
  width: 100%;
  border-radius: 20px;
  box-shadow: 0 4px 18px 0 rgba(0, 0, 0, 0.1);
}

.primary-button {
  background-color: #358E9D;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 1rem 2rem;
  font-size: 1.1rem;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s ease;
}

.primary-button:hover {
  background-color: #286C72;
  transform: translateY(-2px);
}

@keyframes slideInImg {
  0% {
    transform: translateX(100%);
    opacity: 0;
  }
  100% {
    transform: translateX(0);
    opacity: 1;
  }
}

@keyframes slideInLeftContainer {
  0% {
    transform: translateX(-100%);
    opacity: 0;
  }
  100% {
    transform: translateX(0);
    opacity: 1;
  }
}

.second-container {
  display: grid;
  margin-inline: 1.5rem;
  padding-block: 5rem;
  animation: appear linear;
  animation-timeline: view();
  animation-range: 0% cover 30%;
  background-color: white;
}

.card-container{
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(230px, 348px));
  justify-content: center;
  gap: 5rem;
}

.card-article {
  position: relative;
  border: 2px solid grey;
  padding: 3rem 1.5rem 1.5rem;
  display: grid;
  row-gap: 1rem;
  overflow: hidden;
  background-color: #f5f5f5;
  transition: background-color 0.5s ease-in-out;
  height: 350px; /* Hauteur fixe pour toutes les cartes */
  border-radius: 50px;
}

.card-img {
  width: 100%;
  height: 180px; /* Hauteur fixe pour toutes les images */
  object-fit: contain; /* Garde les proportions sans couper l'image */
  filter: drop-shadow(0 8px 24px hsla(170, 12%, 8%, .1));
  transition: transform 1s ease-in-out;
  display: block; /* Évite l'espace supplémentaire sous l'image */
  margin: 0 auto; /* Centre l'image horizontalement */
}

.card-data {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 50px; /* Hauteur fixe pour le titre */
  transition: transform 1s ease-in-out, opacity 1s;
}

.card-title {
  font-weight: 400;
}

.card-bg {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
  transform: scale(1.3);
  z-index: -1;
  transition: transform 1s ease-in-out;
}

.card-btn {
  width: 100%;           
  max-width: 400px;          
  padding: 10px;   
  margin-top: 16px;          
  background-color: #358E9D;
  color: white;
  font-weight: bold;
  font-size: 1rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  text-align: center;
  transition: background-color 0.3s;
  position: absolute;
  bottom: -1.5rem;
  opacity: 0;
  pointer-events: none;
  transition: transform 1s ease-in-out, opacity 1s;
}

/* animation des cards */
.card-article:hover .card-img {
  transform: translateY(-2.5rem);
}

.card-article:hover .card-data {
  transform: translateY(-4.5rem);
  opacity: 0;
}

.card-article:hover {
  background-color: rgba(53, 142, 157, 0); /* Couleur semi-transparente */
}

.card-article:hover .card-bg {
  transform: scale(1);
}

.card-article:hover .card-btn {
  transform: translateY(-3.75rem);
  opacity: 1;
  pointer-events: initial;
}

@keyframes appear {
  from {
    opacity: 0;
    scale: 0.8;
  }
  to {
    opacity: 1;
    scale: 1;
  }
}

.third-container {
  overflow-y: hidden;
  position: relative; /* Référence pour les positions absolues des enfants */
  width: 100%; /* S'assure que le conteneur occupe tout l'espace disponible */
}

.kids-images {
  display: flex;
  justify-content: space-around;
  height: 100px;
  position: relative;
}

.kid-hoop, .little-girl, .baby {
  position: relative; 
  z-index: 1; 
}

.outer {
  position: absolute; /* Par rapport à .fourth-container */
  top: 0; /* Les balles commencent en haut */
  left: 0;
  width: 100%;
  height: 100%;
  background-color: transparent;
}

.ball {
  width: 30px;
  height: 30px;
  position: absolute;
  z-index: 2;
  border-radius: 50%;
  left: -30px;
  background-color: rgb(12, 169, 180);
  top: 0;
  animation: movement 3s linear infinite, squashing 3s linear infinite;
}

.ball:nth-child(2) {
  animation-delay: .1s;
  opacity: .6;
}

@keyframes movement {
  16%, 48%, 78% {
    top: 75px;
    animation-timing-function: ease-out;
  }
  32% {
    top: 20px;
    animation-timing-function: ease-in;
  }
  62% {
    top: 20px;
    animation-timing-function: ease-in;
  }
  90% {
    top: 20px;
    animation-timing-function: ease-in;
  }
  100% {
    top: 75px;
    left: calc(100% - 30px);
    animation-timing-function: eas-out;
    transform: rotate(360deg);
  }
}

@keyframes squashing {
  16%, 48%, 78% {
    height: 25px;
    border-radius: 50px 50px 25px 25px;
    transform: scaleX(1.6);
  }
  15%, 47%, 77%, 18%, 50%, 80%, 100% {
    height: 30px;
    border-radius: 50%;
    transform: scaleX(1);
  }
}

@media (max-width: 1024px) {
  .content-container {
    flex-direction: column;
    gap: 2rem;
  }
  
  .text-content, .image-content {
    width: 100%;
  }
  
  .hero-image {
    max-width: 80%;
    margin: 0 auto;
    display: block;
  }
}

/* Pour les tablettes et mobiles */
@media (max-width: 768px) {
  .image-content {
    display: none; 
  }
  
  .hero-section {
    min-height: 50vh; 
  }
  
  .text-content h1 {
    font-size: 2rem; 
  }
  
  .subtitle {
    font-size: 1rem;
  }
  
  .card-container {
    gap: 3rem; 
  }
  
  .second-container {
    padding-block: 3rem; 
  }
  
  .kids-images {
    height: 80px; 
  }

  .little-girl {
    max-height: 80px;
    width: auto;
  }

  .card-article {
    height: 300px; /* Légèrement plus petit sur mobile */
  }
  
  .card-img {
    height: 150px; /* Légèrement plus petit sur mobile */
  }
  @keyframes movement {
  16%, 48%, 78% {
    top: 60px;
    animation-timing-function: ease-out;
  }
  32% {
    top: 20px;
    animation-timing-function: ease-in;
  }
  62% {
    top: 20px;
    animation-timing-function: ease-in;
  }
  90% {
    top: 20px;
    animation-timing-function: ease-in;
  }
  100% {
    top: 75px;
    left: calc(100% - 30px);
    animation-timing-function: eas-out;
    transform: rotate(360deg);
  }
}
}

/* Ajout pour les très petits écrans mobiles */
@media (max-width: 425px) {
  .card-container {
    grid-template-columns: 1fr; /* Une seule colonne sur les petits écrans */
  }
  
  .card-article {
    width: 90%;
    margin: 0 auto;
    max-width: 280px;
  }
  
  .text-content h1 {
    font-size: 1.75rem;
  }
}
</style>