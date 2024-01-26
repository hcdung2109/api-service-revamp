<?php


namespace Digisource\Candidates\Listeners;


use Digisource\Core\Jobs\BaseEventJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class CandidateCreatedEvent extends BaseEventJob implements ShouldQueue
{

    /**
     * The name of the connection the job should be sent to.
     *
     * @var string|null
     */
    public $connection = 'queue_event';

    /**
     * The name of the queue the job should be sent to.
     *
     * @var string|null
     */
    public $queue = 'candidate_event';

    /**
     * Determine whether the listener should be queued.
     * @param null $repository
     * @param null $entity
     * @return bool
     */
    public function shouldQueue($repository = null, $entity = null): bool
    {
        return true;
    }

    /**
     * @param $event
     * @param $entity
     */
    public function handle($event, $entity)
    {
//        // {product name} #{id}
//        /**
//         * @var $entity Package
//         */
//        $name = $entity->getAttribute('name');
//        $pck_id = $entity->getAttribute('package_id');
//        $id = $entity->getAttribute('id');
//        $entity->pushToNotification($id, "{$name} #{$pck_id}");
//        $this->info('package created',[
//            'data'=>$entity->toArray()
//        ]);
//        $entity->updateDataStatToNew();
//        if($entity->created_by_partner){
//            $entity->createDistributedItemForPartner($entity->created_by_partner, $entity->status , $entity->order, 1);
//        }
//
//        // generate job to optimize thumbnail
//        $featured_image = $entity->featured_image;
//        if (!empty($featured_image)) {
//            $job = new CrawlPackageImageJob([
//                'package_id' => $entity->id,
//                'url' => Arr::get($featured_image, 'raw', ''),
//                'field' => 'featured_image',
//                'index' => 0
//            ]);
//
//            $job->dispatch();
//        }
//        // generate to optimize images
//        if (is_array($entity->featured_image_extra)) {
//            foreach ($entity->featured_image_extra as $i => $image) {
//                $image_url = Arr::get($image ?? [], 'raw', '');
//                if (!empty($image_url)) {
//                    $job = new CrawlPackageImageJob([
//                        'package_id' => $entity->id,
//                        'url' => $image_url,
//                        'field' => 'featured_image_extra',
//                        'index' => $i
//                    ]);
//
//                    $job->dispatch();
//                }
//            }
//        }
//        // generate to optimize images
//        if (is_array($entity->images)) {
//            foreach ($entity->images as $i => $image) {
//                $image_url = Arr::get($image ?? [], 'raw', '');
//                if (!empty($image_url)) {
//                    $job = new CrawlPackageImageJob([
//                        'package_id' => $entity->id,
//                        'url' => $image_url,
//                        'field' => 'images',
//                        'index' => $i
//                    ]);
//
//                    $job->dispatch();
//                }
//            }
//        }
    }
}
