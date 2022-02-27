<?php

namespace App\Entity;

use App\Repository\ChatMessageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ChatMessageRepository::class)
 */
class ChatMessage
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("chatMessage")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @Groups("chatMessage",{"message"})
     */
    private $message_content;

    /**
     * @ORM\ManyToOne(targetEntity=Discussion::class, inversedBy="chatMessages")
     * @ORM\JoinColumn(nullable=false)
     * @Groups("chatMessage")
     */
    private $discussion;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="chatMessages")
     * @ORM\JoinColumn(nullable=false)
     * @Groups("chatMessage")
     */
    private $user;

    /**
     * @ORM\Column(type="datetime")
     */
    private $sending_date;

    public function getId(): int
    {
        return $this->id;
    }

    public function getMessageContent(): ?string
    {
        return $this->message_content;
    }

    public function setMessageContent(string $message_content): self
    {
        $this->message_content = $message_content;

        return $this;
    }

    public function getDiscussion(): ?Discussion
    {
        return $this->discussion;
    }

    public function setDiscussion(?Discussion $discussion): self
    {
        $this->discussion = $discussion;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getSendingDate(): ?\DateTimeInterface
    {
        return $this->sending_date;
    }

    public function setSendingDate(\DateTimeInterface $sending_date): self
    {
        $this->sending_date = $sending_date;

        return $this;
    }
    public function prePersist()
    {
        $this->sending_date = new \DateTime();
    }
}
