<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ["email"], message: "There is already an account with this email")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id;

    #[ORM\Column(type: Types::STRING, length: 180, unique: true)]
    private ?string $email;

    #[ORM\Column(type: Types::JSON)]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column(type: Types::STRING)]
    private string $password;

    #[ORM\Column(type: Types::STRING, length: 50)]
    private ?string $nom;

    #[ORM\Column(type: Types::STRING, length: 50)]
    private ?string $prenom;

    #[ORM\Column(type: Types::STRING, length: 3, nullable: true)]
    private ?string $civilite;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isVerified = false;

    #[ORM\ManyToOne(targetEntity: Departement::class, inversedBy: 'users', fetch: 'EAGER')]
    private ?Departement $departement = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $actif = false;

    /**
     * @var Collection<int, \App\Entity\Departement>
     */
    #[ORM\ManyToMany(targetEntity: Departement::class, inversedBy: 'cpns')]
    private Collection $CpnDepartements;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    private ?string $login;

    /**
     * @var Collection<int, \App\Entity\QapesSae>
     */
    #[ORM\ManyToMany(targetEntity: QapesSae::class, mappedBy: 'auteur')]
    private Collection $qapesSaesAuteurs;

    /**
     * @var Collection<int, \App\Entity\QapesSae>
     */
    #[ORM\ManyToMany(targetEntity: QapesSae::class, mappedBy: 'redacteur')]
    private Collection $qapesSaesRedacteurs;

    public function __construct()
    {
        $this->CpnDepartements = new ArrayCollection();
        $this->qapesSaesAuteurs = new ArrayCollection();
        $this->qapesSaesRedacteurs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        if ($roles === []) {
            $roles[] = 'ROLE_IUT';
        }

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getUsername(): ?string
    {
        return $this->getEmail();
    }

    public function getNom(): ?string
    {
        return mb_strtoupper($this->nom);
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return ucwords(mb_strtolower($this->prenom));
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getCivilite(): ?string
    {
        return $this->civilite;
    }

    public function setCivilite(?string $civilite): self
    {
        $this->civilite = $civilite;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function display(): string
    {
        return ucfirst($this->prenom).' '.mb_strtoupper($this->nom);
    }

    public function __toString(): string
    {
        return $this->display();
    }

    public function getDepartement(): ?Departement
    {
        return $this->departement;
    }

    public function setDepartement(?Departement $departement): self
    {
        $this->departement = $departement;

        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): self
    {
        $this->actif = $actif;

        return $this;
    }

    public function isPacd(): bool
    {
        return in_array('ROLE_PACD', $this->getRoles(), true);
    }

    public function isCpn(): bool
    {
        return in_array('ROLE_CPN', $this->getRoles(), true) || in_array('ROLE_CPN_LECTEUR', $this->getRoles(), true);
    }

    /**
     * @return Collection|Departement[]
     */
    public function getCpnDepartements(): Collection
    {
        return $this->CpnDepartements;
    }

    public function addCpnDepartement(Departement $cpnDepartement): self
    {
        if (!$this->CpnDepartements->contains($cpnDepartement)) {
            $this->CpnDepartements[] = $cpnDepartement;
        }

        return $this;
    }

    public function removeCpnDepartement(Departement $cpnDepartement): self
    {
        $this->CpnDepartements->removeElement($cpnDepartement);

        return $this;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(?string $login): self
    {
        $this->login = $login;

        return $this;
    }

    public function getIsVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function getActif(): ?bool
    {
        return $this->actif;
    }

    /**
     * @return Collection<int, QapesSae>
     */
    public function getQapesSaesAuteurs(): Collection
    {
        return $this->qapesSaesAuteurs;
    }

    public function addQapesSaesAuteur(QapesSae $qapesSaesAuteur): self
    {
        if (!$this->qapesSaesAuteurs->contains($qapesSaesAuteur)) {
            $this->qapesSaesAuteurs[] = $qapesSaesAuteur;
            $qapesSaesAuteur->addAuteur($this);
        }

        return $this;
    }

    public function removeQapesSaesAuteur(QapesSae $qapesSaesAuteur): self
    {
        if ($this->qapesSaesAuteurs->removeElement($qapesSaesAuteur)) {
            $qapesSaesAuteur->removeAuteur($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, QapesSae>
     */
    public function getQapesSaesRedacteurs(): Collection
    {
        return $this->qapesSaesRedacteurs;
    }

    public function addQapesSaesRedacteur(QapesSae $qapesSaesRedacteur): self
    {
        if (!$this->qapesSaesRedacteurs->contains($qapesSaesRedacteur)) {
            $this->qapesSaesRedacteurs[] = $qapesSaesRedacteur;
            $qapesSaesRedacteur->addRedacteur($this);
        }

        return $this;
    }

    public function removeQapesSaesRedacteur(QapesSae $qapesSaesRedacteur): self
    {
        if ($this->qapesSaesRedacteurs->removeElement($qapesSaesRedacteur)) {
            $qapesSaesRedacteur->removeRedacteur($this);
        }

        return $this;
    }
}
