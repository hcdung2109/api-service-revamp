<?php


namespace Digisource\Candidates\Events;

use Illuminate\Contracts\Events\Dispatcher;

class PackageEvents
{
    use Logger;

    const UPDATED_RATE = 'digisource.candidate.candidate.entity.updatedRate';
    const UPDATED = 'digisource.candidate.candidate.entity.updated';
    const CREATED = 'digisource.candidate.candidate.entity.created';

    /**
     * Register the listeners for the subscriber.
     *
     * @param \App\Events\Dispatcher $dispatcher
     * @return void
     */
    public function subscribe(Dispatcher $dispatcher)
    {
//        $dispatcher->listen(
//            static::CREATED,
//            [PackageCreatedEvent::class, 'handle']
//        );
//
//        Event::listen(
//            EventConstant::GET_PACKAGES_DATA,
//            function ($ids, $fields = null) {
//                /**
//                 * @var $repo PackageRepositoryFactory
//                 */
//                $fields = $fields ?? [
//                        'id',
//                        'order',
//                        'status',
//                        'recommended_sort',
//                        'provider_id',
//                        'provider_info',
//                        'created_by_partner'
//                    ];
//                $repo = app()->make(PackageRepositoryFactory::class);
//                return $repo->whereIn('id', $ids)->findAll($fields);
//            }
//        );
    }
}
