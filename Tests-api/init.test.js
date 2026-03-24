const axios = require('axios');

const Axios = axios.create({
    baseURL: 'http://localhost:8000/api',
    headers: {
        Accept: 'application/json'
    }
});

// LOGIN
async function authenticate(user, credentials) {
  const res = await Axios.get('/sanctum/csrf-cookie', {
      baseURL: 'http://localhost:8000'
  });

  Axios.defaults.headers.cookie = res.headers['set-cookie'];
  Axios.defaults.headers.common['X-XSRF-TOKEN'] = parseCSRFToken(res.headers['set-cookie']);
  Axios.defaults.headers.common['Origin'] = 'http://localhost:8000';
  Axios.defaults.headers.common['Referer'] = 'http://localhost:8000';

  const auth = await Axios.post('/login', credentials, {
      baseURL: 'http://localhost:8000',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
  });

  Axios.defaults.headers.cookie = auth.headers['set-cookie'];
  Axios.defaults.headers.common['X-XSRF-TOKEN'] = parseCSRFToken(auth.headers['set-cookie'])

  for (let key in auth.data.user) {
      user[key] = auth.data.user[key];
  }
}

function parseCSRFToken(cookies) {
  const startAt = cookies[0].indexOf('=');
  const endAt = cookies[0].indexOf(';');
  const csrf = cookies[0].substring(startAt + 1, endAt - 3);
  return csrf;
}

// TEST LOGIN AND USER INFO
const user = {};

describe("Authentication and User Info", () => {
    beforeEach(async () => {
        // Se connecter avant chaque test
        await authenticate(user, {
            email: 'parent@jp.fr',
            password: 'Azerty88@'
        });
    });

    test('Should login successfully', () => {
        expect(user).toHaveProperty('email', 'parent@jp.fr');
    });

    test('Should get user info', async () => {
        try {
            const response = await Axios.get('/user');
            expect(response.status).toBe(200);
            expect(response.data).toHaveProperty('email');
            expect(response.data).toHaveProperty('first_name');
            console.log('User info:', response.data);
        } catch (error) {
            console.error('Test error:', error.response?.data || error.message);
            throw error;
        }
    });
});

// Tests pour la gestion des enfants
describe("Kids Management", () => {
    // Test existant de récupération de la liste
    test('Should fetch kids list', async () => {
        const response = await Axios.get('/mykids');
        expect(response.status).toBe(200);
        expect(Array.isArray(response.data)).toBe(true);
        console.log('Kids list:', response.data);
    });
  
    // Nouveaux tests pour couvrir les fonctionnalités CRUD complètes
    test('Should fetch details of a specific kid', async () => {
        // Récupérer d'abord la liste des enfants pour obtenir un ID valide
        const kidsList = await Axios.get('/mykids');
        const kidId = kidsList.data[0]?.id || 1; // Utiliser le premier enfant ou par défaut l'ID 1
        
        const response = await Axios.get(`/mykid/${kidId}`);
        expect(response.status).toBe(200);
        expect(response.data).toHaveProperty('first_name');
        expect(response.data).toHaveProperty('birth_date');
        console.log('Kid details:', response.data);
    });
  
    test('Should create a new kid', async () => {
        const kidData = {
            first_name: 'Test Child',
            birth_date: '2020-01-01'
        };
        
        const response = await Axios.post('/createkid', kidData);
        expect(response.status).toBe(201);
        expect(response.data).toHaveProperty('id');
        expect(response.data.first_name).toBe('Test Child');
        console.log('Created kid:', response.data);
        
        // Stocker l'ID pour les tests suivants
        global.testKidId = response.data.id;
    });
  
    test('Should update a kid', async () => {
        // Utiliser l'ID stocké lors de la création ou un ID par défaut
        const kidId = global.testKidId || 1;
        const updateData = {
            first_name: 'Updated Name'
        };
        
        const response = await Axios.put(`/updatekid/${kidId}`, updateData);
        expect(response.status).toBe(200);
        expect(response.data.kid.first_name).toBe('Updated Name');
        console.log('Updated kid:', response.data);
    });
  
    test('Should delete a kid', async () => {
        // Utiliser l'ID stocké lors de la création ou créer un nouveau kid pour le supprimer
        let kidId;
        if (!global.testKidId) {
            const createResponse = await Axios.post('/createkid', {
                first_name: 'To Delete',
                birth_date: '2020-02-02'
            });
            kidId = createResponse.data.id;
        } else {
            kidId = global.testKidId;
        }
        
        const response = await Axios.delete(`/deletekid/${kidId}`);
        expect(response.status).toBe(200);
        expect(response.data).toHaveProperty('message');
        console.log('Delete response:', response.data);
    });
  });
  
  // Tests supplémentaires pour les opérations d'enregistrement
  describe("Record Operations", () => {
    // Tests existants
    
    // Nouveaux tests
    test('Should handle error when starting record for non-existent kid', async () => {
        try {
            await Axios.post('/999/record/start'); // ID qui n'existe probablement pas
            // Si la requête réussit, le test échoue
            expect(true).toBe(false); 
        } catch (error) {
            expect(error.response.status).toBe(404);
            console.log('Error response:', error.response.data);
        }
    });
  
    test('Should handle error when stopping non-existent record', async () => {
        try {
            await Axios.post('/999/record/stop'); // ID qui n'existe probablement pas
            // Si la requête réussit, le test échoue
            expect(true).toBe(false); 
        } catch (error) {
            expect(error.response.status).toBe(404);
            console.log('Error response:', error.response.data);
        }
    });
  
    test('Should not allow starting a record when one is already active', async () => {
        // Récupérer la liste des enfants
        const kidsList = await Axios.get('/mykids');
        if (kidsList.data.length === 0) {
            console.log('No kids available for testing');
            return;
        }
        
        const kidId = kidsList.data[0].id;
        
        try {
            // Démarrer un premier enregistrement
            await Axios.post(`/${kidId}/record/start`);
            
            // Essayer de démarrer un second enregistrement (devrait échouer)
            await Axios.post(`/${kidId}/record/start`);
            // Si la deuxième requête réussit, le test échoue
            expect(true).toBe(false);
        } catch (error) {
            expect(error.response.status).toBe(400);
            console.log('Error response:', error.response.data);
            
            // Nettoyer : arrêter l'enregistrement
            await Axios.post(`/${kidId}/record/stop`);
        }
    });
  });
  
  // Tests pour les pauses
  describe("Break Management", () => {
    test('Should start a break during an active record', async () => {
        // Créer un enregistrement actif d'abord
        const kidsList = await Axios.get('/mykids');
        if (kidsList.data.length === 0) {
            console.log('No kids available for testing');
            return;
        }
        
        const kidId = kidsList.data[0].id;
        
        // Démarrer un enregistrement
        const startResponse = await Axios.post(`/${kidId}/record/start`);
        const recordId = startResponse.data.record.id;
        
        // Démarrer une pause
        const breakData = {
            record_id: recordId
        };
        
        const response = await Axios.post('/timebreaks/start', breakData);
        expect(response.status).toBe(201);
        expect(response.data).toHaveProperty('timeBreak');
        expect(response.data.timeBreak).toHaveProperty('break_start');
        console.log('Break started:', response.data);
        
        // Stocker l'ID de la pause pour le test suivant
        global.testBreakId = response.data.timeBreak.id;
        global.testRecordId = recordId;
    });
  
    test('Should end an active break', async () => {
        // Utiliser l'ID de la pause stocké lors du test précédent ou abandonner le test
        if (!global.testBreakId || !global.testRecordId) {
            console.log('No break or record available for testing');
            return;
        }
        
        // Modifier le format des données pour correspondre à ce que le contrôleur attend
        const breakData = {
            record_id: global.testRecordId  // Utiliser l'ID de l'enregistrement, pas celui de la pause
        };
        
        const response = await Axios.post('/timebreaks/end', breakData);
        expect(response.status).toBe(200);
        expect(response.data).toHaveProperty('timeBreak');
        expect(response.data.timeBreak).toHaveProperty('break_end');
        console.log('Break ended:', response.data);
        
        // Nettoyer : arrêter l'enregistrement actif
        if (global.testRecordId) {
            await Axios.post(`/1/record/stop`); // Utiliser l'ID du kid actif
        }
    });
  
    test('Should check if a record has an active break', async () => {
        // Créer un enregistrement et une pause si nécessaire pour tester
        const kidsList = await Axios.get('/mykids');
        if (kidsList.data.length === 0) {
            console.log('No kids available for testing');
            return;
        }
        
        const kidId = kidsList.data[0].id;
        
        // D'abord, vérifiez s'il y a un enregistrement actif et arrêtez-le
        try {
            await Axios.post(`/${kidId}/record/stop`);
            console.log('Stopped existing active record');
        } catch (error) {
            // Ignorer l'erreur si aucun enregistrement actif n'existe
            console.log('No active record to stop');
        }
        
        // Démarrer un enregistrement
        const startResponse = await Axios.post(`/${kidId}/record/start`);
        const recordId = startResponse.data.record.id;
        
        // Vérifier s'il y a une pause active (ne devrait pas y en avoir)
        let response = await Axios.get(`/timebreaks/check/${recordId}`);
        expect(response.status).toBe(200);
        expect(response.data).toHaveProperty('hasActiveBreak', false);
        
        // Démarrer une pause
        await Axios.post('/timebreaks/start', { record_id: recordId });
        
        // Vérifier à nouveau (devrait y avoir une pause active)
        response = await Axios.get(`/timebreaks/check/${recordId}`);
        expect(response.status).toBe(200);
        expect(response.data).toHaveProperty('hasActiveBreak', true);
        
        // Nettoyer : arrêter l'enregistrement (et sa pause)
        await Axios.post(`/${kidId}/record/stop`);
    });
  });
  
  // Tests pour les connexions entre utilisateurs
  describe("User Connections", () => {
    test('Should get list of connected users', async () => {
        const response = await Axios.get('/connected-users');
        expect(response.status).toBe(200);
        expect(response.data).toHaveProperty('connected_users');
        console.log('Connected users:', response.data);
    });
  
    test('Should get received connection requests', async () => {
        const response = await Axios.get('/connection-requests/received');
        expect(response.status).toBe(200);
        expect(response.data).toHaveProperty('requests');
        console.log('Received requests:', response.data);
    });
  
    test('Should get sent connection requests', async () => {
        const response = await Axios.get('/connection-requests/sent');
        expect(response.status).toBe(200);
        expect(response.data).toHaveProperty('requests');
        console.log('Sent requests:', response.data);
    });
  
    // Test conditionnel - ne s'exécute que s'il y a une demande de connexion en attente
    test('Should accept a connection request if available', async () => {
        const receivedRequests = await Axios.get('/connection-requests/received');
        const pendingRequest = receivedRequests.data.requests.find(req => req.status === 'pending');
        
        if (pendingRequest) {
            const response = await Axios.post(`/connection-requests/${pendingRequest.id}/accept`);
            expect(response.status).toBe(200);
            expect(response.data).toHaveProperty('message');
            console.log('Accept response:', response.data);
        } else {
            console.log('No pending requests to accept');
        }
    });
  
    // Test conditionnel - ne s'exécute que s'il y a des doublons potentiels
    test('Should check for potential duplicate kids with a connected user', async () => {
        const connectedUsers = await Axios.get('/connected-users');
        if (connectedUsers.data.connected_users.length === 0) {
            console.log('No connected users available for testing');
            return;
        }
        
        const userId = connectedUsers.data.connected_users[0].id;
        const response = await Axios.get(`/check-duplicates-with-user/${userId}`);
        expect(response.status).toBe(200);
        expect(response.data).toHaveProperty('potential_duplicates');
        console.log('Potential duplicates:', response.data);
    });
  
    // Test conditionnel - ne s'exécute que s'il y a un utilisateur connecté et un enfant à partager
    test('Should share a kid with a connected user', async () => {
        const [connectedUsers, myKids] = await Promise.all([
            Axios.get('/connected-users'),
            Axios.get('/mykids')
        ]);
        
        if (connectedUsers.data.connected_users.length === 0 || myKids.data.length === 0) {
            console.log('No connected users or kids available for testing');
            return;
        }
        
        const userId = connectedUsers.data.connected_users[0].id;
        const kidId = myKids.data[0].id;
        
        const shareData = {
            kid_id: kidId,
            partner_id: userId
        };
        
        try {
            const response = await Axios.post('/share-kid-with-partner', shareData);
            expect(response.status).toBe(200);
            expect(response.data).toHaveProperty('message');
            console.log('Share response:', response.data);
        } catch (error) {
            // Ce pourrait échouer si l'enfant est déjà partagé
            if (error.response.status === 400) {
                console.log('Kid might already be shared:', error.response.data);
            } else {
                throw error;
            }
        }
    });
  });
  
  // Tests additionnels pour le profil utilisateur
  describe("User Profile Management", () => {
    test('Should update user profile', async () => {
        const updateData = {
            first_name: user.first_name, // Conserver le même prénom
            city: 'Test City', // Mettre à jour la ville
            zip_code: '12345' // Mettre à jour le code postal
        };
        
        const response = await Axios.put('/user', updateData);
        expect(response.status).toBe(200);
        expect(response.data).toHaveProperty('message');
        expect(response.data.user.city).toBe('Test City');
        console.log('Profile update response:', response.data);
    });
  
    // Ce test est commenté car il supprime le compte - à n'exécuter qu'avec un compte de test
    /*
    test('Should delete user account', async () => {
        const response = await Axios.delete('/user/delete');
        expect(response.status).toBe(200);
        expect(response.data).toHaveProperty('message');
        console.log('Account deletion response:', response.data);
    });
    */
  });
  
  // Tests de validation des erreurs et cas limites
  describe("Error Handling", () => {
    test('Should handle invalid login credentials', async () => {
        try {
            // Créer un nouvel axios pour ne pas perturber la session en cours
            const testAxios = axios.create({
                baseURL: 'http://localhost:8000',
                headers: { Accept: 'application/json' }
            });
            
            // Obtenir le cookie CSRF
            const res = await testAxios.get('/sanctum/csrf-cookie');
            const csrf = parseCSRFToken(res.headers['set-cookie']);
            
            // Tentative de connexion avec des identifiants invalides
            await testAxios.post('/login', 
                { email: 'wrong@email.com', password: 'wrongpassword' },
                {
                    headers: {
                        'X-XSRF-TOKEN': csrf,
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'Origin': 'http://localhost:8000',
                        'Referer': 'http://localhost:8000'
                    }
                }
            );
            
            // La requête ne devrait pas réussir
            expect(true).toBe(false);
        } catch (error) {
            // Vérifier que l'erreur existe, sans se préoccuper du code spécifique
            expect(error.response).toBeTruthy();
            console.log('Invalid login error status:', error.response.status);
            console.log('Invalid login error:', error.response.data);
        }
    });
  
    test('Should handle validation errors when creating a kid', async () => {
        try {
            // Envoyer des données incomplètes
            await Axios.post('/createkid', { 
                // Manque first_name qui est requis
                birth_date: '2020-01-01'
            });
            
            // La requête ne devrait pas réussir
            expect(true).toBe(false);
        } catch (error) {
            expect(error.response.status).toBe(422);
            expect(error.response.data).toHaveProperty('errors');
            expect(error.response.data.errors).toHaveProperty('first_name');
            console.log('Validation error:', error.response.data);
        }
    });
  });
