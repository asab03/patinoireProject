<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ProjectRepository::class)
 */
class Project
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_in;

    /**
    *@Assert\NotBlank
    * @var string
    * @Assert\Date
    */
    private $start_date_str;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_out;

    /**
    *@Assert\NotBlank
    * @var string
    * @Assert\Date
    */
    private $end_date_str;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="projects")
     */
    
    private $user;

    /**
     * @ORM\OneToMany(targetEntity=Expense::class, mappedBy="project")
     */
    private $expenses;

    /**
     * @ORM\OneToMany(targetEntity=Document::class, mappedBy="project")
     */
    private $documents;
    /**
     * @ORM\OneToOne(targetEntity=Discussion::class,cascade={"persist"}, mappedBy="project",orphanRemoval=true)
     */
    private $discussion;

    public function __construct()
    {
        $this->user = new ArrayCollection();
        $this->expenses = new ArrayCollection();
        $this->documents = new ArrayCollection();
        // $this->discussion = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDateIn(): ?\DateTimeInterface
    {
        return $this->date_in;
    }

    public function setDateIn(\DateTimeInterface $date_in): self
    {
        $this->date_in = $date_in;

        return $this;
    }

    public function setStartDateStr(string $start_date_str)
    {
        $this -> start_date_str =$start_date_str;
        return $this;
    }

    public function getDateOut(): ?\DateTimeInterface
    {
        return $this->date_out;
    }

    public function setDateOut(\DateTimeInterface $date_out): self
    {
        $this->date_out = $date_out;

        return $this;
    }

    public function setEndDateStr(string $end_date_str)
    {
        $this -> end_date_str = $end_date_str;
        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUser(): Collection
    {
        return $this->user;
    }

    public function addUser(User $user): self
    {
        if (!$this->user->contains($user)) {
            $this->user[] = $user;
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        $this->user->removeElement($user);

        return $this;
    }
    
    public function clearUsers(): self
    {
        foreach($this->user as $user) {
            $this->removeUser($user);
        }
        
        return $this;
    }

    /**
     * @return Collection|Expense[]
     */
    public function getExpenses(): Collection
    {
        return $this->expenses;
    }

    public function addExpense(Expense $expense): self
    {
        if (!$this->expenses->contains($expense)) {
            $this->expenses[] = $expense;
            $expense->setProject($this);
        }

        return $this;
    }

    public function removeExpense(Expense $expense): self
    {
        if ($this->expenses->removeElement($expense)) {
            // set the owning side to null (unless already changed)
            if ($expense->getProject() === $this) {
                $expense->setProject(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Document[]
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents[] = $document;
            $document->setProject($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getProject() === $this) {
                $document->setProject(null);
            }
        }

        return $this;
    }
    /**
     * @return Discussion|null
     */
    public function getDiscussion()
    {
        return $this->discussion;
    }

    public function setDiscussion(Discussion $discussion): self
    {
        $this->discussion = $discussion;
        return $this;
    }

    
}
