<?php

namespace DefStudio\Telegraph\Concerns;

use Closure;
use DefStudio\Telegraph\DTO\Conversations\Step;
use DefStudio\Telegraph\DTO\Message;
use DefStudio\Telegraph\Handlers\Conversation;
use Laravel\SerializableClosure\SerializableClosure;

trait HandlesConversations
{
    use HasStorage;

    public ?Step $step;

    public function getConversationIdentifier(): string
    {
        return 'conversation';
    }

    public function startConversation(Conversation $instance): void
    {
        $instance->chat = $this;
        $instance->start();
    }

    public function storeConversation(Conversation $conversation, Closure $next, string $question/*, $additionalParameters = []*/)
    {
        //        $conversation_cache_time = $instance->getConversationCacheTime();
        //
        $this->step = new Step();

        $this->step->question = $question;

        $preparedConversation = clone $conversation;
        $preparedConversation->chat = null;
        $this->step->conversation = $preparedConversation;

        $preparedNext = clone $next;
        $preparedNext = $preparedNext->bindTo($preparedConversation, $preparedConversation);
        $this->step->next = $preparedNext;

        $this->storage()->set($this->getConversationIdentifier(), $this->step->toArray());

        //            'question' => serialize($question),
        //            'additionalParameters' => serialize($additionalParameters),
        //            'time' => microtime(),
        //        ], $conversation_cache_time ?? $this->config['config']['conversation_cache_time'] ?? 30);
    }

    /**
     * Touch and update the current conversation.
     *
     * @return void
     */
    public function touchCurrentConversation()
    {
        if (!is_null($this->currentConversationData)) {
            $touched = $this->currentConversationData;
            $touched['time'] = microtime();

            $this->cache->put($this->message->getConversationIdentifier(), $touched, $this->config['config']['conversation_cache_time'] ?? 30);
        }
    }

    /**
     * Remove a stored conversation array from the cache for a given message.
     *
     * @param null|IncomingMessage $message
     */
    public function removeStoredConversation($message = null)
    {
        $conversation = $this->getStoredConversation($message);

        //        if (isset($conversation['time']) && ($conversation['time'] == $this->currentConversationData['time'])) {
        //            $this->cache->pull($this->message->getConversationIdentifier());
        //            $this->cache->pull($this->message->getOriginatedConversationIdentifier());
        //        }
    }

    public function hasConversation(): bool
    {
        // todo implement storage()->has() method
        return !is_null($this->storage()->get($this->getConversationIdentifier()));
    }

    public function handleConversation(Message $message): void
    {
        //        $this->loadedConversation = false;

        $this->step = Step::fromArray(
            $this->storage()->get($this->getConversationIdentifier())
        );
        $this->step->setChat($this);

        // Should we skip the conversation?
        //        if ($convo['conversation']->skipsConversation($message) === true) {
        //            return;
        //        }

        // Or stop it entirely?
        //        if ($convo['conversation']->stopsConversation($message) === true) {
        //            $this->cache->pull($message->getConversationIdentifier());
        //            $this->cache->pull($message->getOriginatedConversationIdentifier());
        //
        //            return;
        //        }

        // todo remove cache
        //        $this->removeStoredConversation();
        $this->storage()->forget($this->getConversationIdentifier());


        //            $matchingMessages = $this->conversationManager->getMatchingMessages([$message], $this->middleware, $this->getConversationAnswer(), $this->getDriver(), false);
        //            foreach ($matchingMessages as $matchingMessage) {
        //                $command = $matchingMessage->getCommand();
        //                if ($command->shouldStopConversation()) {
        //                    $this->cache->pull($message->getConversationIdentifier());
        //                    $this->cache->pull($message->getOriginatedConversationIdentifier());
        //
        //                    return;
        //                } elseif ($command->shouldSkipConversation()) {
        //                    return;
        //                }
        //            }

        // Ongoing conversation - let's find the callback.
        //            $parameters = [];
        //            if ($next) {
        //                $toRepeat = false;
        //                foreach ($convo['next'] as $callback) {
        //                    if ($this->matcher->isPatternValid($message, $this->getConversationAnswer(), $callback['pattern'])) {
        //                        $parameterNames = $this->compileParameterNames($callback['pattern']);
        //                        $matches = $this->matcher->getMatches();

        //                        if (count($parameterNames) === count($matches)) {
        //                            $parameters = array_combine($parameterNames, $matches);
        //                        } else {
        //                            $parameters = $matches;
        //                        }
        //                        $this->matches = $parameters;
        //                        $next = $this->unserializeClosure($callback['callback']);
        //                        break;
        //                    }
        //                }

        //                if ($next == false) {
        //no pattern match
        //answer probably unexpected (some plain text)
        //let's repeat question
        //                    $toRepeat = true;
        //                }
        //            } else {
        //                $next = $this->unserializeClosure($convo['next']);
        //            }

        //            $this->message = $message;
        //            $this->currentConversationData = $convo;


        // todo
        //        $toRepeat = !$next;
        //        if ($toRepeat) {
        //            ray($toRepeat);
        //            $conversation = $convo['conversation'];
        //            $conversation->setBot($this);
        //            $conversation->repeat();
        //            $this->loadedConversation = true;
        //        }

        //        $this->callConversation($next, $conversation, $message/*, $parameters*/);


        $next = $this->step->next;
        $next($message);

        // todo
        //        $this->removeStoredConversation();
    }

    protected function callConversation(Closure $next, Conversation $conversation, Message $message/*, array $parameters*/)
    {
        // todo
        //        if (!$conversation instanceof ShouldQueue) {
        //            $conversation->setBot($this);
        //        }

        /*
         * Validate askForImages, askForAudio, etc. calls
         */
        //        $additionalParameters = Collection::make(unserialize($convo['additionalParameters']));
        //        if ($additionalParameters->has('__pattern')) {
        //            if ($this->matcher->isPatternValid($message, $this->getConversationAnswer(), $additionalParameters->get('__pattern'))) {
        //                $getter = $additionalParameters->get('__getter');
        //                array_unshift($parameters, $this->getConversationAnswer()->getMessage()->$getter());
        //                $this->prepareConversationClosure($next, $conversation, $parameters);
        //            } else {
        //                if (is_null($additionalParameters->get('__repeat'))) {
        //                    $conversation->repeat();
        //                } else {
        //                    $next = unserialize($additionalParameters->get('__repeat'));
        //                    array_unshift($parameters, $this->getConversationAnswer());
        //                    $this->prepareConversationClosure($next, $conversation, $parameters);
        //                }
        //            }
        //        } else {
        //            array_unshift($parameters, $this->getConversationAnswer());
        //            $this->prepareConversationClosure($next, $conversation, $parameters);
        //        }

        // Mark conversation as loaded to avoid triggering the fallback method
        //        $this->loadedConversation = true;
    }

    /**
     * @param Closure $next
     * @param Conversation $conversation
     * @param array $parameters
     */
    protected function prepareConversationClosure($next, Conversation $conversation, array $parameters)
    {
        if ($next instanceof SerializableClosure) {
            $next = $next->getClosure()->bindTo($conversation, $conversation);
        } elseif ($next instanceof Closure) {
            $next = $next->bindTo($conversation, $conversation);
        }

        $parameters[] = $conversation;

        call_user_func_array($next, array_values($parameters));

        /*
        // TODO: Needs more work
        if (class_exists('Illuminate\\Support\\Facades\\App')) {
            \Illuminate\Support\Facades\App::call($next, $parameters);
        } else {
            call_user_func_array($next, array_values($parameters));
        }
        // */
    }
}
