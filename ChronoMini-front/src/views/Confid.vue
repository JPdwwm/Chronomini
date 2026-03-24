<script setup lang="ts">

</script>

<template>
  <div class="privacy-container">
    <div class="privacy-content">
      <h1>Politique de confidentialité</h1>
      
      <section class="privacy-section">
        <h2>Collecte de l'information</h2>
        <p>Lorsque vous visitez notre site, vous demeurez anonyme. Les informations collectées au cours de votre navigation servent à analyser votre comportement sur ce site et l'usage que vous en avez afin d'améliorer votre expérience utilisateur.</p>
        <p>Les autres données personnelles que nous collectons, tels que vos nom, prénom, adresse postale ou adresse mail, ne sont collectées que si vous acceptez de nous les fournir, par exemple lorsque vous vous créez un compte ou lorsque vous ajoutez les informations relatives aux enfants gardés.</p>
        <p>Nous nous engageons par ailleurs à ne recueillir que les informations strictement nécessaires au calcul des heures de garde et à la gestion de votre compte.</p>
      </section>
      
      <section class="privacy-section">
        <h2>Droit d'accès, de modification et de suppression de vos données</h2>
        <p>Vous avez le droit de ne pas vouloir nous communiquer de données personnelles. Dans ce cas, vous pourrez naviguer sur ce site, cependant vous n'aurez pas accès à l'ensemble de ses fonctionnalités, telles que le calcul des heures de garde et la génération de récapitulatifs.</p>
        <p>Nous nous engageons à vous offrir un droit d'opposition et de retrait quant à vos renseignements personnels. Le droit d'opposition s'entend comme étant la possibilité offerte aux utilisateurs de refuser que leurs renseignements personnels soient utilisés à certaines fins mentionnées lors de la collecte.</p>
        <p>Si, à tout moment, vous souhaitez avoir accès aux informations personnelles vous concernant, ou si vous souhaitez les modifier ou les supprimer, vous pouvez le faire directement depuis votre espace personnel ou en faire la demande par e-mail à l'adresse contact@chronomini.fr</p>
      </section>
      
      <section class="privacy-section">
        <h2>Utilisation des informations</h2>
        <p>Nous nous engageons à respecter la stricte confidentialité de vos données personnelles et de les utiliser uniquement pour les finalités pour lesquelles vous avez accepté de nous les communiquer. Ces données ne seront pas conservées plus longtemps que nécessaire, et seront supprimées dès lors que vous supprimez votre compte.</p>
      </section>
      
      <section class="privacy-section">
        <h2>Protection des informations et données personnelles</h2>
        <p>Nous nous engageons à respecter les dispositions de la loi n°78-17 du 6 janvier 1978 relative à l'informatique, aux fichiers et aux libertés, et prendre toute précaution nécessaire pour préserver la sécurité des informations confiées.</p>
        <p>Aucune information à caractère personnel ne sera communiquée à des sociétés tierces sans l'accord préalable et éclairé de l'utilisateur.</p>
        <p>Nous protégeons également vos informations hors ligne. Les ordinateurs et serveurs utilisés pour stocker des informations personnelles identifiables sont conservés dans un environnement sécurisé.</p>
      </section>
      
      <section class="privacy-section">
        <h2>Informations relatives aux cookies sur notre site</h2>
        <p>Lors de vos visites sur le site, un cookie peut s'installer automatiquement sur votre logiciel de navigation. Un cookie est un élément qui ne permet pas de vous identifier, mais sert à enregistrer des informations relatives à votre navigation sur le site Internet. Vous pouvez désactiver ce cookie par l'intermédiaire des paramètres figurant au sein de votre logiciel de navigation.</p>
      </section>
    </div>
  </div>
</template>

<style scoped>
.privacy-container {
  min-height: 90vh;
  padding: 2rem;
  display: flex;
  justify-content: center;
  background-color: #f5f5f5;
  font-family: Georgia, 'Times New Roman', Times, serif;
}

.privacy-content {
  max-width: 800px;
  width: 100%;
  background-color: white;
  padding: 2rem;
  border-radius: 20px;
  box-shadow: 0 4px 18px 0 rgba(0, 0, 0, 0.1);
  animation: fadeIn 0.8s ease;
}

h1 {
  color: #78BB99;
  font-size: 2.2rem;
  margin-bottom: 2rem;
  text-align: center;
  font-weight: bold;
}

.privacy-section {
  margin-bottom: 2rem;
}

h2 {
  color: #358E9D;
  font-size: 1.5rem;
  margin-bottom: 1rem;
  border-bottom: 2px solid #F7CD79;
  padding-bottom: 0.5rem;
}

p {
  color: #666;
  font-size: 1rem;
  line-height: 1.6;
  margin-bottom: 1rem;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Pour les tablettes */
@media (max-width: 768px) {
  .privacy-container {
    padding: 1.5rem;
  }
  
  .privacy-content {
    padding: 1.5rem;
  }
  
  h1 {
    font-size: 1.8rem;
  }
  
  h2 {
    font-size: 1.3rem;
  }
}

/* Pour les mobiles */
@media (max-width: 425px) {
  .privacy-container {
    padding: 1rem;
  }
  
  .privacy-content {
    padding: 1rem;
    border-radius: 10px;
  }
  
  h1 {
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
  }
  
  h2 {
    font-size: 1.2rem;
  }
  
  p {
    font-size: 0.9rem;
  }
}
</style>