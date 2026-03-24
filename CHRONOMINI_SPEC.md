# ChronoMini - Spécification Technique Complète

> Document de spécification pour la reconstruction du projet ChronoMini en **Java Spring Boot** (backend) et **Angular** (frontend).

---

## 1. Vue d'ensemble du projet

### Description
ChronoMini est une application de **gestion du temps de garde d'enfants** permettant aux parents et assistants maternels (asmats) de :
- Enregistrer les heures de dépôt et de récupération des enfants
- Gérer les pauses (repas, siestes, etc.)
- Partager la gestion des enfants entre plusieurs utilisateurs
- Se connecter entre parents et asmats via un système de codes de liaison

### Architecture cible
```
┌─────────────────────────────────────────────────────────────┐
│                        Frontend                              │
│                    Angular 17+ / TypeScript                  │
│                         Port: 4200                           │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼ HTTP/REST + JWT
┌─────────────────────────────────────────────────────────────┐
│                         Backend                              │
│                  Spring Boot 3.x / Java 21                   │
│                         Port: 8080                           │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼ JPA/Hibernate
┌─────────────────────────────────────────────────────────────┐
│                        Database                              │
│              MySQL 8.x / H2 (dev) / PostgreSQL               │
│                         Port: 3306                           │
└─────────────────────────────────────────────────────────────┘
```

---

## 2. Modèles de données (Entités JPA)

### 2.1 Role
```java
@Entity
@Table(name = "roles")
public class Role {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @Column(nullable = false, unique = true)
    private String name;

    @OneToMany(mappedBy = "role")
    private List<User> users;
}
```

**Données initiales :**
| id | name |
|----|------|
| 1 | Admin |
| 2 | Parent |
| 3 | Asmat |

---

### 2.2 User
```java
@Entity
@Table(name = "users")
public class User {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @Column(name = "first_name", nullable = false)
    private String firstName;

    @Column(name = "last_name", nullable = false)
    private String lastName;

    @Column(nullable = false, unique = true)
    private String email;

    @Column(nullable = false)
    private String password; // BCrypt hashé

    @Column(name = "zip_code")
    private String zipCode;

    private String city;

    @ManyToOne(fetch = FetchType.EAGER)
    @JoinColumn(name = "role_id", nullable = false)
    private Role role;

    @Column(name = "link_code", unique = true, length = 5)
    private String linkCode; // Code unique pour les connexions entre utilisateurs

    @Column(length = 40)
    private String token; // Token de vérification email

    @Column(name = "email_verified_at")
    private LocalDateTime emailVerifiedAt;

    @Column(name = "created_at")
    private LocalDateTime createdAt;

    @Column(name = "updated_at")
    private LocalDateTime updatedAt;

    // Relations
    @OneToMany(mappedBy = "user", cascade = CascadeType.ALL)
    private List<Record> records;

    @OneToMany(mappedBy = "sender")
    private List<ConnectionRequest> sentRequests;

    @OneToMany(mappedBy = "receiver")
    private List<ConnectionRequest> receivedRequests;

    @OneToMany(mappedBy = "user", cascade = CascadeType.ALL)
    private List<KidUser> kidUsers;

    // Méthodes utilitaires
    public boolean isAdmin() {
        return this.role.getId() == 1;
    }

    public boolean isParent() {
        return this.role.getId() == 2;
    }

    public boolean isAsmat() {
        return this.role.getId() == 3;
    }
}
```

---

### 2.3 Kid
```java
@Entity
@Table(name = "kids")
public class Kid {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @Column(name = "first_name", nullable = false)
    private String firstName;

    @Column(name = "birth_date", nullable = false)
    private LocalDate birthDate;

    @Column(name = "created_at")
    private LocalDateTime createdAt;

    @Column(name = "updated_at")
    private LocalDateTime updatedAt;

    // Relations
    @OneToMany(mappedBy = "kid", cascade = CascadeType.ALL)
    private List<Record> records;

    @OneToMany(mappedBy = "kid", cascade = CascadeType.ALL)
    private List<KidUser> kidUsers;
}
```

---

### 2.4 KidUser (Table pivot avec attributs)
```java
@Entity
@Table(name = "kid_user", uniqueConstraints = {
    @UniqueConstraint(columnNames = {"kid_id", "user_id"})
})
public class KidUser {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "kid_id", nullable = false)
    private Kid kid;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "user_id", nullable = false)
    private User user;

    @Column(name = "access_type", nullable = false)
    @Enumerated(EnumType.STRING)
    private AccessType accessType; // FULL ou READONLY

    @Column(name = "start_date")
    private LocalDate startDate;

    @Column(name = "end_date")
    private LocalDate endDate;

    @Column(name = "created_at")
    private LocalDateTime createdAt;

    @Column(name = "updated_at")
    private LocalDateTime updatedAt;
}

public enum AccessType {
    FULL,    // Lecture et écriture
    READONLY // Lecture seule (limité par end_date)
}
```

---

### 2.5 Record (Enregistrement de temps)
```java
@Entity
@Table(name = "records")
public class Record {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "user_id", nullable = false)
    private User user; // Créateur de l'enregistrement

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "kid_id", nullable = false)
    private Kid kid;

    @Column(name = "drop_hour", nullable = false)
    private LocalTime dropHour; // Heure de dépôt

    @Column(name = "pick_up_hour")
    private LocalTime pickUpHour; // Heure de récupération (null si en cours)

    @Column(name = "amount_hours")
    private Double amountHours; // Durée totale en heures décimales (pauses déduites)

    private String annotation; // Note optionnelle

    @Column(nullable = false)
    private LocalDate date;

    @Column(name = "created_at")
    private LocalDateTime createdAt;

    @Column(name = "updated_at")
    private LocalDateTime updatedAt;

    // Relations
    @OneToMany(mappedBy = "record", cascade = CascadeType.ALL)
    private List<TimeBreak> timeBreaks;

    // Méthode pour vérifier si l'enregistrement est actif
    public boolean isActive() {
        return this.pickUpHour == null;
    }
}
```

---

### 2.6 TimeBreak (Pause)
```java
@Entity
@Table(name = "time_breaks")
public class TimeBreak {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "record_id", nullable = false)
    private Record record;

    @Column(name = "break_start", nullable = false)
    private LocalTime breakStart;

    @Column(name = "break_end")
    private LocalTime breakEnd; // null si pause en cours

    private Integer total; // Durée en minutes

    @Column(name = "created_at")
    private LocalDateTime createdAt;

    @Column(name = "updated_at")
    private LocalDateTime updatedAt;

    public boolean isActive() {
        return this.breakEnd == null;
    }
}
```

---

### 2.7 ConnectionRequest (Demande de connexion)
```java
@Entity
@Table(name = "connection_requests", uniqueConstraints = {
    @UniqueConstraint(columnNames = {"sender_id", "receiver_id"})
})
public class ConnectionRequest {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "sender_id", nullable = false)
    private User sender;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "receiver_id", nullable = false)
    private User receiver;

    @Column(nullable = false)
    @Enumerated(EnumType.STRING)
    private ConnectionStatus status;

    @Column(name = "created_at")
    private LocalDateTime createdAt;

    @Column(name = "updated_at")
    private LocalDateTime updatedAt;
}

public enum ConnectionStatus {
    PENDING,
    ACCEPTED,
    DECLINED,
    DISCONNECTED
}
```

---

## 3. Diagramme des relations

```
┌─────────────────┐
│      Role       │
├─────────────────┤
│ id              │
│ name            │
└────────┬────────┘
         │ 1
         │
         │ *
┌────────▼────────┐       ┌─────────────────┐
│      User       │◄──────┤ConnectionRequest│
├─────────────────┤   *   ├─────────────────┤
│ id              │       │ id              │
│ firstName       │───────► sender_id       │
│ lastName        │   *   │ receiver_id     │
│ email           │       │ status          │
│ password        │       └─────────────────┘
│ role_id         │
│ linkCode        │
│ token           │
│ emailVerifiedAt │
└────────┬────────┘
         │
         │ * (via KidUser)
         │
┌────────▼────────┐
│    KidUser      │
├─────────────────┤
│ id              │
│ kid_id          │
│ user_id         │
│ accessType      │
│ startDate       │
│ endDate         │
└────────┬────────┘
         │
         │ *
┌────────▼────────┐       ┌─────────────────┐
│      Kid        │       │     Record      │
├─────────────────┤   1   ├─────────────────┤
│ id              │◄──────┤ id              │
│ firstName       │   *   │ user_id         │
│ birthDate       │       │ kid_id          │
└─────────────────┘       │ dropHour        │
                          │ pickUpHour      │
                          │ amountHours     │
                          │ annotation      │
                          │ date            │
                          └────────┬────────┘
                                   │ 1
                                   │
                                   │ *
                          ┌────────▼────────┐
                          │   TimeBreak     │
                          ├─────────────────┤
                          │ id              │
                          │ record_id       │
                          │ breakStart      │
                          │ breakEnd        │
                          │ total           │
                          └─────────────────┘
```

---

## 4. API Endpoints

### 4.1 Authentification (`/api/auth`)

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| POST | `/register` | Inscription utilisateur | Non |
| POST | `/login` | Connexion (retourne JWT) | Non |
| POST | `/logout` | Déconnexion | Oui |
| POST | `/verification` | Vérification email | Non |
| POST | `/forgot-password` | Demande reset mot de passe | Non |
| POST | `/reset-password` | Reset mot de passe | Non |

**POST /register - Body :**
```json
{
    "firstName": "string",
    "lastName": "string",
    "email": "string",
    "password": "string",
    "roleId": 2, // 2=Parent, 3=Asmat
    "zipCode": "string (optionnel)",
    "city": "string (optionnel)"
}
```

**POST /verification - Body :**
```json
{
    "email": "string",
    "token": "string"
}
```

---

### 4.2 Profil utilisateur (`/api/user`)

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| GET | `/` | Récupérer le profil courant | Oui |
| PUT | `/` | Mettre à jour son profil | Oui |
| DELETE | `/` | Supprimer son compte | Oui |

---

### 4.3 Gestion des enfants (`/api/kids`)

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| GET | `/my` | Récupérer ses enfants | Oui |
| GET | `/{id}` | Détails d'un enfant | Oui |
| POST | `/` | Créer un enfant | Oui |
| PUT | `/{id}` | Modifier un enfant | Oui |
| DELETE | `/{id}` | Supprimer/détacher un enfant | Oui |

**POST / - Body :**
```json
{
    "firstName": "string",
    "birthDate": "YYYY-MM-DD"
}
```

---

### 4.4 Enregistrements de temps (`/api/records`)

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| GET | `/my` | Mes enregistrements (paginés, 31/page) | Oui |
| GET | `/{id}` | Détails d'un enregistrement | Oui |
| GET | `/kid/{kidId}` | Enregistrements d'un enfant | Oui |
| POST | `/kid/{kidId}/start` | Démarrer un enregistrement (dépôt) | Oui |
| POST | `/kid/{kidId}/stop` | Terminer un enregistrement (récup) | Oui |
| POST | `/{id}/annotation` | Ajouter/modifier une annotation | Oui |
| DELETE | `/{id}` | Supprimer un enregistrement | Oui |

**POST /kid/{kidId}/start - Body :**
```json
{
    "dropHour": "HH:mm"
}
```

**POST /kid/{kidId}/stop - Body :**
```json
{
    "pickUpHour": "HH:mm"
}
```

**Logique de calcul `amountHours` :**
```java
// Calcul de la durée totale
double totalMinutes = ChronoUnit.MINUTES.between(dropHour, pickUpHour);

// Déduction des pauses
int breakMinutes = timeBreaks.stream()
    .filter(tb -> tb.getTotal() != null)
    .mapToInt(TimeBreak::getTotal)
    .sum();

double amountHours = (totalMinutes - breakMinutes) / 60.0;
```

---

### 4.5 Pauses (`/api/timebreaks`)

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| POST | `/start` | Démarrer une pause | Oui |
| POST | `/end` | Terminer une pause | Oui |
| GET | `/check/{recordId}` | Vérifier si pause active | Oui |

**POST /start - Body :**
```json
{
    "recordId": 1,
    "breakStart": "HH:mm"
}
```

**POST /end - Body :**
```json
{
    "recordId": 1,
    "breakEnd": "HH:mm"
}
```

---

### 4.6 Connexions utilisateurs (`/api/connections`)

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| POST | `/request` | Envoyer demande de connexion | Oui |
| GET | `/requests/received` | Demandes reçues (pending) | Oui |
| GET | `/requests/sent` | Demandes envoyées | Oui |
| POST | `/requests/{id}/accept` | Accepter une demande | Oui |
| POST | `/requests/{id}/decline` | Refuser une demande | Oui |
| GET | `/users` | Utilisateurs connectés | Oui |
| POST | `/disconnect` | Se déconnecter d'un utilisateur | Oui |

**POST /request - Body :**
```json
{
    "linkCode": "ABC12"
}
```

---

### 4.7 Partage d'enfants (`/api/sharing`)

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| POST | `/share-kid` | Partager un enfant avec un partenaire | Oui |
| POST | `/merge-kids` | Fusionner des enfants en double | Oui |
| GET | `/check-duplicates/{userId}` | Vérifier les doublons avec un utilisateur | Oui |

**POST /share-kid - Body :**
```json
{
    "kidId": 1,
    "partnerId": 2,
    "accessType": "FULL" // ou "READONLY"
}
```

**POST /merge-kids - Body :**
```json
{
    "keepKidId": 1,
    "removeKidId": 2
}
```

---

### 4.8 Administration (`/api/admin`)

> **Middleware requis :** Authentifié + Rôle Admin

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| GET | `/users` | Liste tous les utilisateurs | Admin |
| GET | `/users/{id}` | Détails d'un utilisateur | Admin |
| PUT | `/users/{id}` | Modifier un utilisateur | Admin |
| DELETE | `/users/{id}` | Supprimer un utilisateur | Admin |
| GET | `/records` | Liste tous les enregistrements | Admin |
| GET | `/kids` | Liste tous les enfants | Admin |

---

## 5. Authentification & Autorisation

### 5.1 Configuration Spring Security avec JWT

**Dépendances Maven :**
```xml
<dependency>
    <groupId>org.springframework.boot</groupId>
    <artifactId>spring-boot-starter-security</artifactId>
</dependency>
<dependency>
    <groupId>io.jsonwebtoken</groupId>
    <artifactId>jjwt-api</artifactId>
    <version>0.12.3</version>
</dependency>
<dependency>
    <groupId>io.jsonwebtoken</groupId>
    <artifactId>jjwt-impl</artifactId>
    <version>0.12.3</version>
    <scope>runtime</scope>
</dependency>
<dependency>
    <groupId>io.jsonwebtoken</groupId>
    <artifactId>jjwt-jackson</artifactId>
    <version>0.12.3</version>
    <scope>runtime</scope>
</dependency>
```

### 5.2 Flux d'authentification

```
┌─────────┐     POST /login        ┌─────────┐
│ Client  │ ────────────────────►  │ Backend │
└─────────┘   email + password     └────┬────┘
                                        │
                                        ▼
                               Vérification email vérifié?
                                        │
                                        ▼
                               Vérification password BCrypt
                                        │
                                        ▼
                               Génération JWT (24h validité)
                                        │
┌─────────┐     200 + JWT          ┌────▼────┐
│ Client  │ ◄────────────────────  │ Backend │
└─────────┘   + user object        └─────────┘
```

### 5.3 Structure JWT

```json
{
    "sub": "user@email.com",
    "userId": 1,
    "roleId": 2,
    "iat": 1234567890,
    "exp": 1234654290
}
```

### 5.4 Policies d'autorisation

#### RecordPolicy
```java
@Component
public class RecordPolicy {

    public boolean canViewAll(User user) {
        return user.isAdmin();
    }

    public boolean canShowOne(User user, Record record) {
        // L'utilisateur doit avoir accès au kid
        KidUser kidUser = findKidUserRelation(user, record.getKid());
        if (kidUser == null && !user.isAdmin()) return false;

        // Si readonly, vérifier les dates
        if (kidUser.getAccessType() == AccessType.READONLY) {
            return record.getDate().isBefore(kidUser.getEndDate()) ||
                   record.getDate().isEqual(kidUser.getEndDate());
        }
        return true;
    }

    public boolean canStartRecording(User user, Kid kid) {
        KidUser kidUser = findKidUserRelation(user, kid);
        return kidUser != null &&
               kidUser.getAccessType() == AccessType.FULL &&
               (user.isParent() || user.isAsmat());
    }

    public boolean canStopRecording(User user, Kid kid) {
        return canStartRecording(user, kid);
    }

    public boolean canAddAnnotation(User user, Record record) {
        KidUser kidUser = findKidUserRelation(user, record.getKid());
        return kidUser != null && kidUser.getAccessType() == AccessType.FULL;
    }

    public boolean canDelete(User user, Record record) {
        if (user.isAdmin()) return true;

        KidUser kidUser = findKidUserRelation(user, record.getKid());
        return record.getUser().getId().equals(user.getId()) &&
               kidUser != null &&
               kidUser.getAccessType() == AccessType.FULL;
    }
}
```

#### KidPolicy
```java
@Component
public class KidPolicy {

    public boolean canViewAllKids(User user) {
        return user.isAdmin();
    }

    public boolean canShowOne(User user, Kid kid) {
        return user.isAdmin() || hasAnyAccess(user, kid);
    }

    public boolean canUpdate(User user, Kid kid) {
        if (user.isAdmin()) return true;

        KidUser kidUser = findKidUserRelation(user, kid);
        if (kidUser == null) return false;

        // Parent qui possède l'enfant OU Asmat avec accès full
        return (user.isParent() && kidUser.getAccessType() == AccessType.FULL) ||
               (user.isAsmat() && kidUser.getAccessType() == AccessType.FULL);
    }

    public boolean canDelete(User user, Kid kid) {
        return canUpdate(user, kid);
    }
}
```

---

## 6. Règles métier

### 6.1 Génération du linkCode

```java
public String generateLinkCode() {
    String chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    SecureRandom random = new SecureRandom();
    StringBuilder code = new StringBuilder(5);

    do {
        code.setLength(0);
        for (int i = 0; i < 5; i++) {
            code.append(chars.charAt(random.nextInt(chars.length())));
        }
    } while (userRepository.existsByLinkCode(code.toString()));

    return code.toString();
}
```

### 6.2 Vérification d'email

1. À l'inscription, générer un token aléatoire de 40 caractères
2. Envoyer un email avec le lien de vérification
3. L'utilisateur ne peut pas se connecter tant que `emailVerifiedAt` est null
4. Après vérification, mettre à jour `emailVerifiedAt` et supprimer le token

### 6.3 Enregistrement de temps - Contraintes

- **Un seul enregistrement actif par enfant** : Avant de créer un record, vérifier qu'il n'existe pas de record avec `pickUpHour = null` pour cet enfant
- **Les pauses doivent être dans la plage horaire** : `breakStart >= dropHour` et `breakEnd <= pickUpHour`
- **Fermeture automatique des pauses** : Quand on termine un record, fermer toutes les pauses ouvertes

### 6.4 Demandes de connexion - Règles

- Un utilisateur ne peut pas s'envoyer une demande à lui-même
- Pas de demande en doublon (vérifier sender+receiver unique)
- Connexions compatibles :
  - Parent ↔ Asmat ✅
  - Parent ↔ Parent ✅
  - Asmat ↔ Asmat ✅
- Une demande `DECLINED` peut être renvoyée plus tard

### 6.5 Suppression en cascade

Lors de la suppression d'un **User** :
1. Supprimer tous les `ConnectionRequest` (sender ou receiver)
2. Supprimer toutes les relations `KidUser`
3. Supprimer tous les `Record` créés par cet utilisateur
4. Les enfants restent s'ils sont liés à d'autres utilisateurs

---

## 7. DTOs (Data Transfer Objects)

### 7.1 Request DTOs

```java
// Auth
public record RegisterRequest(
    String firstName,
    String lastName,
    String email,
    String password,
    Long roleId,
    String zipCode,
    String city
) {}

public record LoginRequest(String email, String password) {}

// Kids
public record CreateKidRequest(String firstName, LocalDate birthDate) {}
public record UpdateKidRequest(String firstName, LocalDate birthDate) {}

// Records
public record StartRecordRequest(LocalTime dropHour) {}
public record StopRecordRequest(LocalTime pickUpHour) {}
public record AnnotationRequest(String annotation) {}

// TimeBreaks
public record StartBreakRequest(Long recordId, LocalTime breakStart) {}
public record EndBreakRequest(Long recordId, LocalTime breakEnd) {}

// Connections
public record ConnectionRequestDto(String linkCode) {}
public record DisconnectRequest(Long userId) {}
public record ShareKidRequest(Long kidId, Long partnerId, AccessType accessType) {}
public record MergeKidsRequest(Long keepKidId, Long removeKidId) {}
```

### 7.2 Response DTOs

```java
public record UserResponse(
    Long id,
    String firstName,
    String lastName,
    String email,
    String zipCode,
    String city,
    Long roleId,
    String linkCode,
    LocalDateTime emailVerifiedAt
) {}

public record KidResponse(
    Long id,
    String firstName,
    LocalDate birthDate,
    AccessType accessType // Pour le user courant
) {}

public record RecordResponse(
    Long id,
    Long userId,
    Long kidId,
    String kidName,
    LocalTime dropHour,
    LocalTime pickUpHour,
    Double amountHours,
    String annotation,
    LocalDate date,
    List<TimeBreakResponse> timeBreaks
) {}

public record TimeBreakResponse(
    Long id,
    LocalTime breakStart,
    LocalTime breakEnd,
    Integer total
) {}

public record ConnectionRequestResponse(
    Long id,
    UserResponse sender,
    UserResponse receiver,
    ConnectionStatus status,
    LocalDateTime createdAt
) {}

public record AuthResponse(
    String token,
    UserResponse user
) {}

public record PagedResponse<T>(
    List<T> content,
    int page,
    int size,
    long totalElements,
    int totalPages
) {}
```

---

## 8. Configuration Spring Boot

### 8.1 application.yml

```yaml
spring:
  application:
    name: chronomini-api

  datasource:
    url: jdbc:mysql://localhost:3306/chronomini
    username: ${DB_USERNAME:root}
    password: ${DB_PASSWORD:}
    driver-class-name: com.mysql.cj.jdbc.Driver

  jpa:
    hibernate:
      ddl-auto: validate
    show-sql: false
    properties:
      hibernate:
        dialect: org.hibernate.dialect.MySQLDialect
        format_sql: true

  mail:
    host: ${MAIL_HOST:smtp.mailtrap.io}
    port: ${MAIL_PORT:587}
    username: ${MAIL_USERNAME}
    password: ${MAIL_PASSWORD}
    properties:
      mail.smtp.auth: true
      mail.smtp.starttls.enable: true

app:
  jwt:
    secret: ${JWT_SECRET:your-super-secret-key-min-256-bits}
    expiration: 86400000 # 24 heures en ms

  cors:
    allowed-origins: http://localhost:4200

  mail:
    from-address: noreply@chronomini.fr
    from-name: ChronoMini

server:
  port: 8080
```

### 8.2 application-dev.yml (Profil développement)

```yaml
spring:
  datasource:
    url: jdbc:h2:mem:chronomini
    driver-class-name: org.h2.Driver

  h2:
    console:
      enabled: true
      path: /h2-console

  jpa:
    hibernate:
      ddl-auto: create-drop
    show-sql: true

  mail:
    host: localhost
    port: 1025 # MailHog ou similaire
```

---

## 9. Structure Angular

### 9.1 Architecture des dossiers

```
src/
├── app/
│   ├── core/
│   │   ├── guards/
│   │   │   ├── auth.guard.ts
│   │   │   └── admin.guard.ts
│   │   ├── interceptors/
│   │   │   ├── auth.interceptor.ts
│   │   │   └── error.interceptor.ts
│   │   ├── services/
│   │   │   ├── auth.service.ts
│   │   │   ├── user.service.ts
│   │   │   ├── kid.service.ts
│   │   │   ├── record.service.ts
│   │   │   ├── connection.service.ts
│   │   │   └── admin.service.ts
│   │   └── models/
│   │       ├── user.model.ts
│   │       ├── kid.model.ts
│   │       ├── record.model.ts
│   │       └── ...
│   │
│   ├── shared/
│   │   ├── components/
│   │   │   ├── header/
│   │   │   ├── footer/
│   │   │   ├── loading-spinner/
│   │   │   └── ...
│   │   ├── directives/
│   │   └── pipes/
│   │
│   ├── features/
│   │   ├── auth/
│   │   │   ├── login/
│   │   │   ├── register/
│   │   │   └── verify-email/
│   │   │
│   │   ├── dashboard/
│   │   │   └── dashboard.component.ts
│   │   │
│   │   ├── kids/
│   │   │   ├── kid-list/
│   │   │   ├── kid-detail/
│   │   │   └── kid-form/
│   │   │
│   │   ├── records/
│   │   │   ├── record-list/
│   │   │   ├── record-detail/
│   │   │   └── record-timer/
│   │   │
│   │   ├── connections/
│   │   │   ├── connection-list/
│   │   │   └── connection-requests/
│   │   │
│   │   ├── profile/
│   │   │   └── profile.component.ts
│   │   │
│   │   └── admin/
│   │       ├── user-management/
│   │       └── admin-dashboard/
│   │
│   ├── app.component.ts
│   ├── app.config.ts
│   └── app.routes.ts
│
├── assets/
│   ├── i18n/
│   │   ├── fr.json
│   │   └── en.json
│   └── images/
│
├── environments/
│   ├── environment.ts
│   └── environment.prod.ts
│
└── styles.scss
```

### 9.2 Modèles TypeScript

```typescript
// user.model.ts
export interface User {
  id: number;
  firstName: string;
  lastName: string;
  email: string;
  zipCode?: string;
  city?: string;
  roleId: number;
  linkCode: string;
  emailVerifiedAt?: string;
}

export interface AuthResponse {
  token: string;
  user: User;
}

// kid.model.ts
export interface Kid {
  id: number;
  firstName: string;
  birthDate: string;
  accessType?: 'FULL' | 'READONLY';
}

// record.model.ts
export interface Record {
  id: number;
  userId: number;
  kidId: number;
  kidName: string;
  dropHour: string;
  pickUpHour?: string;
  amountHours?: number;
  annotation?: string;
  date: string;
  timeBreaks: TimeBreak[];
}

export interface TimeBreak {
  id: number;
  breakStart: string;
  breakEnd?: string;
  total?: number;
}

// connection.model.ts
export interface ConnectionRequest {
  id: number;
  sender: User;
  receiver: User;
  status: 'PENDING' | 'ACCEPTED' | 'DECLINED' | 'DISCONNECTED';
  createdAt: string;
}
```

### 9.3 Service d'authentification

```typescript
// auth.service.ts
@Injectable({ providedIn: 'root' })
export class AuthService {
  private readonly API_URL = environment.apiUrl;
  private currentUserSubject = new BehaviorSubject<User | null>(null);
  public currentUser$ = this.currentUserSubject.asObservable();

  constructor(private http: HttpClient) {
    this.loadUserFromStorage();
  }

  login(email: string, password: string): Observable<AuthResponse> {
    return this.http.post<AuthResponse>(`${this.API_URL}/auth/login`, { email, password })
      .pipe(
        tap(response => {
          localStorage.setItem('token', response.token);
          localStorage.setItem('user', JSON.stringify(response.user));
          this.currentUserSubject.next(response.user);
        })
      );
  }

  register(data: RegisterRequest): Observable<void> {
    return this.http.post<void>(`${this.API_URL}/auth/register`, data);
  }

  logout(): void {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    this.currentUserSubject.next(null);
  }

  get isAuthenticated(): boolean {
    return !!this.currentUserSubject.value;
  }

  get isAdmin(): boolean {
    return this.currentUserSubject.value?.roleId === 1;
  }

  private loadUserFromStorage(): void {
    const userJson = localStorage.getItem('user');
    if (userJson) {
      this.currentUserSubject.next(JSON.parse(userJson));
    }
  }
}
```

### 9.4 Intercepteur HTTP

```typescript
// auth.interceptor.ts
export const authInterceptor: HttpInterceptorFn = (req, next) => {
  const token = localStorage.getItem('token');

  if (token) {
    req = req.clone({
      setHeaders: {
        Authorization: `Bearer ${token}`
      }
    });
  }

  return next(req);
};
```

### 9.5 Configuration des routes

```typescript
// app.routes.ts
export const routes: Routes = [
  { path: '', redirectTo: '/dashboard', pathMatch: 'full' },

  // Auth (public)
  { path: 'login', loadComponent: () => import('./features/auth/login/login.component') },
  { path: 'register', loadComponent: () => import('./features/auth/register/register.component') },
  { path: 'verify-email', loadComponent: () => import('./features/auth/verify-email/verify-email.component') },

  // Protected routes
  {
    path: 'dashboard',
    loadComponent: () => import('./features/dashboard/dashboard.component'),
    canActivate: [authGuard]
  },
  {
    path: 'kids',
    loadChildren: () => import('./features/kids/kids.routes'),
    canActivate: [authGuard]
  },
  {
    path: 'records',
    loadChildren: () => import('./features/records/records.routes'),
    canActivate: [authGuard]
  },
  {
    path: 'connections',
    loadChildren: () => import('./features/connections/connections.routes'),
    canActivate: [authGuard]
  },
  {
    path: 'profile',
    loadComponent: () => import('./features/profile/profile.component'),
    canActivate: [authGuard]
  },

  // Admin routes
  {
    path: 'admin',
    loadChildren: () => import('./features/admin/admin.routes'),
    canActivate: [authGuard, adminGuard]
  },

  { path: '**', redirectTo: '/dashboard' }
];
```

---

## 10. Internationalisation (i18n)

### Structure des fichiers de traduction

```json
// fr.json
{
  "common": {
    "save": "Enregistrer",
    "cancel": "Annuler",
    "delete": "Supprimer",
    "edit": "Modifier",
    "confirm": "Confirmer"
  },
  "auth": {
    "login": "Connexion",
    "register": "Inscription",
    "logout": "Déconnexion",
    "email": "Email",
    "password": "Mot de passe",
    "forgotPassword": "Mot de passe oublié ?",
    "verifyEmail": "Vérifiez votre email"
  },
  "kids": {
    "title": "Mes enfants",
    "addKid": "Ajouter un enfant",
    "firstName": "Prénom",
    "birthDate": "Date de naissance"
  },
  "records": {
    "title": "Enregistrements",
    "startRecord": "Déposer",
    "stopRecord": "Récupérer",
    "dropHour": "Heure de dépôt",
    "pickUpHour": "Heure de récupération",
    "duration": "Durée",
    "annotation": "Note"
  },
  "connections": {
    "title": "Mes connexions",
    "linkCode": "Mon code",
    "sendRequest": "Envoyer une demande",
    "pendingRequests": "Demandes en attente",
    "accept": "Accepter",
    "decline": "Refuser"
  },
  "roles": {
    "admin": "Administrateur",
    "parent": "Parent",
    "asmat": "Assistant(e) maternel(le)"
  }
}
```

---

## 11. Tests

### 11.1 Tests Backend (JUnit + Mockito)

```java
@SpringBootTest
@AutoConfigureMockMvc
class RecordControllerTest {

    @Autowired
    private MockMvc mockMvc;

    @MockBean
    private RecordService recordService;

    @Test
    @WithMockUser
    void startRecord_WithValidData_ReturnsCreated() throws Exception {
        // Given
        Long kidId = 1L;
        StartRecordRequest request = new StartRecordRequest(LocalTime.of(8, 30));

        // When & Then
        mockMvc.perform(post("/api/records/kid/{kidId}/start", kidId)
                .contentType(MediaType.APPLICATION_JSON)
                .content(objectMapper.writeValueAsString(request)))
            .andExpect(status().isCreated());
    }
}
```

### 11.2 Tests Frontend (Jasmine/Karma ou Jest)

```typescript
describe('AuthService', () => {
  let service: AuthService;
  let httpMock: HttpTestingController;

  beforeEach(() => {
    TestBed.configureTestingModule({
      imports: [HttpClientTestingModule],
      providers: [AuthService]
    });
    service = TestBed.inject(AuthService);
    httpMock = TestBed.inject(HttpTestingController);
  });

  it('should login and store token', () => {
    const mockResponse: AuthResponse = {
      token: 'test-token',
      user: { id: 1, firstName: 'Test', lastName: 'User', email: 'test@test.com', roleId: 2, linkCode: 'ABC12' }
    };

    service.login('test@test.com', 'password').subscribe(response => {
      expect(response.token).toBe('test-token');
      expect(localStorage.getItem('token')).toBe('test-token');
    });

    const req = httpMock.expectOne(`${environment.apiUrl}/auth/login`);
    expect(req.request.method).toBe('POST');
    req.flush(mockResponse);
  });
});
```

---

## 12. Docker Configuration

### docker-compose.yml

```yaml
version: '3.8'

services:
  backend:
    build:
      context: ./backend
      dockerfile: Dockerfile
    ports:
      - "8080:8080"
    environment:
      - SPRING_PROFILES_ACTIVE=prod
      - DB_HOST=database
      - DB_PORT=3306
      - DB_NAME=chronomini
      - DB_USERNAME=chronomini
      - DB_PASSWORD=${DB_PASSWORD}
      - JWT_SECRET=${JWT_SECRET}
    depends_on:
      database:
        condition: service_healthy

  frontend:
    build:
      context: ./frontend
      dockerfile: Dockerfile
    ports:
      - "80:80"
    depends_on:
      - backend

  database:
    image: mysql:8.0
    environment:
      - MYSQL_DATABASE=chronomini
      - MYSQL_USER=chronomini
      - MYSQL_PASSWORD=${DB_PASSWORD}
      - MYSQL_ROOT_PASSWORD=${DB_ROOT_PASSWORD}
    volumes:
      - mysql_data:/var/lib/mysql
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
      interval: 10s
      timeout: 5s
      retries: 5

volumes:
  mysql_data:
```

### Backend Dockerfile

```dockerfile
FROM eclipse-temurin:21-jdk-alpine AS build
WORKDIR /app
COPY mvnw pom.xml ./
COPY .mvn .mvn
RUN ./mvnw dependency:go-offline
COPY src src
RUN ./mvnw package -DskipTests

FROM eclipse-temurin:21-jre-alpine
WORKDIR /app
COPY --from=build /app/target/*.jar app.jar
EXPOSE 8080
ENTRYPOINT ["java", "-jar", "app.jar"]
```

### Frontend Dockerfile

```dockerfile
FROM node:20-alpine AS build
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

FROM nginx:alpine
COPY --from=build /app/dist/chronomini-front /usr/share/nginx/html
COPY nginx.conf /etc/nginx/conf.d/default.conf
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
```

---

## 13. Checklist d'implémentation

### Backend (Spring Boot)
- [ ] Initialiser le projet avec Spring Initializr (Web, Security, JPA, Validation, Mail)
- [ ] Configurer la base de données et Flyway/Liquibase pour les migrations
- [ ] Créer les entités JPA
- [ ] Créer les repositories
- [ ] Implémenter JWT authentication
- [ ] Créer les services métier
- [ ] Créer les controllers REST
- [ ] Implémenter les policies d'autorisation
- [ ] Configurer CORS
- [ ] Ajouter la validation des DTOs
- [ ] Implémenter l'envoi d'emails
- [ ] Écrire les tests unitaires et d'intégration
- [ ] Configurer Docker

### Frontend (Angular)
- [ ] Créer le projet avec Angular CLI
- [ ] Configurer le routing
- [ ] Créer les modèles TypeScript
- [ ] Implémenter les services HTTP
- [ ] Créer l'intercepteur d'authentification
- [ ] Implémenter les guards
- [ ] Créer les composants d'authentification
- [ ] Créer le dashboard
- [ ] Créer la gestion des enfants
- [ ] Créer la gestion des enregistrements avec timer
- [ ] Créer la gestion des connexions
- [ ] Créer le profil utilisateur
- [ ] Créer le panel admin
- [ ] Configurer i18n (fr/en)
- [ ] Ajouter les styles et l'UI
- [ ] Écrire les tests
- [ ] Configurer Docker

---

## 14. Ressources utiles

- [Spring Boot Documentation](https://docs.spring.io/spring-boot/docs/current/reference/html/)
- [Spring Security JWT Guide](https://www.baeldung.com/spring-security-oauth-jwt)
- [Angular Documentation](https://angular.io/docs)
- [Angular Material](https://material.angular.io/) (optionnel pour l'UI)
- [ngx-translate](https://github.com/ngx-translate/core) (alternative à Angular i18n)

---

*Document généré pour la migration de ChronoMini vers Java Spring Boot / Angular*
