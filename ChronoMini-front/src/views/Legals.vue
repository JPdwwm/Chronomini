<script setup lang="ts">

</script>

<template>
  <div class="legal-container">
    <div class="legal-content">
      <h1>Mentions légales</h1>
      
      <section class="legal-section">
        <h2>Éditeur du site</h2>
        <p>Gripacus Jean-Philippe</p>
        <p>Email : contact@chronomini.fr</p>
      </section>
      
      <section class="legal-section">
        <h2>Hébergeur du site</h2>
        <p>Raison sociale : HOSTINGER operations, UAB</p>
        <p>Adresse : Švitrigailos str. 34, Vilnius 03230 Lithuania</p>
        <p>E-mail : domains@hostinger.com</p>
      </section>
      
      <section class="legal-section">
        <h2>Conditions générales d'utilisation</h2>
        <p>Pour un meilleur confort d'utilisation, nous vous recommandons de recourir à des navigateurs modernes comme Chrome, Firefox, Safari.</p>
        <p>Nous mettons en œuvre tous les moyens dont nous disposons pour assurer une information fiable et une mise à jour fiable du site internet. Toutefois, des erreurs ou omissions peuvent survenir. Nous ne sommes en aucun cas responsable de l'utilisation faite de ces informations et de tout préjudice direct ou indirect pouvant en découler.</p>
        <p>ChronoMini met à disposition un système de pointage permettant de calculer les heures de garde. Ce système repose sur l’action volontaire de l’utilisateur au moment de la dépose et de la récupération de l’enfant.</p>
        <p>En cas d’oubli de pointage, d’utilisation incorrecte, ou de tout autre comportement entraînant une erreur dans les relevés horaires, l’éditeur de l’application ne pourra être tenu responsable des écarts constatés. Les relevés générés par l’application sont fournis à titre indicatif et doivent être vérifiés par les parties concernées.</p>
        <p>L’utilisateur reste responsable de l’exactitude des données transmises. ChronoMini est un outil de facilitation et ne remplace pas un accord contractuel ou une validation conjointe entre les utilisateurs.</p>
      </section>
      
      <section class="legal-section">
        <h2>Propriété intellectuelle</h2>
        <p>Tout le contenu présent sur le site incluant les images, textes, animations ainsi que leur mise en forme sont la propriété exclusive de Jean-Philippe Gripacus à l'exception des marques, logos ou contenus pouvant appartenir à d'autres sociétés.</p>
        <p>Les polices "Gloria Hallelujah" et "Georgia" sont utilisées selon leurs licences respectives.</p>
        <p>Toute reproduction, distribution, modification, adaptation, retransmission ou publication, même partielle, de ces différents éléments est strictement interdite sans l'accord par écrit de Jean-Philippe Gripacus. Cette reproduction, par quelque procédé que ce soit, constitue une contrefaçon sanctionnée par les articles L.335-2 et suivants du Code de la propriété intellectuelle.</p>
      </section>
      
      <section class="legal-section">
        <h2>Données personnelles</h2>
        <p>Nous collectons de manière automatique certaines informations vous concernant lors d'une simple navigation sur notre site, notamment : des informations concernant l'utilisation de notre site, comme les zones que vous visitez, le type de votre navigateur ou vos temps d'accès. Ces informations sont utilisées exclusivement à des fins de statistiques d'audience.</p>
        <p>Les informations relatives aux enfants et aux heures de garde sont protégées et ne sont accessibles qu'aux personnes autorisées (parents et assistantes maternelles concernés).</p>
        <p>Par ailleurs, les bases de données sont protégées par les dispositions de la loi du 1er juillet 1998 transposant la directive 96/9 du 11 mars 1996 relative à la protection juridique des bases de données.</p>
      </section>
    </div>
  </div>
</template>

<style scoped>
.legal-container {
  min-height: 90vh;
  padding: 2rem;
  display: flex;
  justify-content: center;
  background-color: #f5f5f5;
  font-family: Georgia, 'Times New Roman', Times, serif;
}

.legal-content {
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

.legal-section {
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
  .legal-container {
    padding: 1.5rem;
  }
  
  .legal-content {
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
  .legal-container {
    padding: 1rem;
  }
  
  .legal-content {
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