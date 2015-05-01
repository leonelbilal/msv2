<?php

namespace MuzikSpirit\BackBundle\Controller;

use MuzikSpirit\BackBundle\Entity\News;
use MuzikSpirit\BackBundle\Utilities\Youtube;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
class AdminController extends Controller
{
    public function YoutubeAction($id){
        // set video data feed URL
        $feedURL = 'http://gdata.youtube.com/feeds/api/videos/' . $id;

        // read feed into SimpleXML object
        $entry = simplexml_load_file($feedURL);

        // parse video entry
        $video = Youtube::parseVideoEntry($entry);
        $image = 'http://i3.ytimg.com/vi/'.$id.'/default.jpg';
        $title = $video->title;

        $data = <<<EOF
<videos>
    <video>
        <Title>$title</Title>
        <Image>$image</Image>
    </video>
</videos>
EOF;
        $response = new Response();
        $response->setContent($data);
        $response->headers->set('Content-Type', 'application/xml');

        return $response;
    }
    public function DailymotionAction($id){
        $feedURL = 'http://www.dailymotion.com/atom/video/'.$id;
        $xml = simplexml_load_file($feedURL);

        $title = preg_replace('/Dailymotion - /','',$xml->title);

        $image = "http://www.dailymotion.com/thumbnail/video/".$id;

        $data = <<<EOF
<videos>
    <video>
        <Title>$title</Title>
        <Image>$image</Image>
    </video>
</videos>
EOF;
        $response = new Response();
        $response->setContent($data);
        $response->headers->set('Content-Type', 'application/xml');

        return $response;
    }

    public function VimeoAction($id){
        $feedURL = 'http://vimeo.com/api/v2/video/'.$id.'.xml';
        $xml = simplexml_load_file($feedURL);
        $title =  $xml->video->title;
        $image =  $xml->video->thumbnail_medium;

        $data = <<<EOF
<videos>
    <video>
        <Title>$title</Title>
        <Image>$image</Image>
    </video>
</videos>
EOF;
        $response = new Response();
        $response->setContent($data);
        $response->headers->set('Content-Type', 'application/xml');

        return $response;
    }

    public function PreviewAction(Request $request){
        if ($request->getMethod() == 'POST') {
            $media = $request->request->get('media');
        }

        return $this->render('MuzikSpiritBackBundle:Admin:preview.html.twig',
            array(
                'media' => $media,
            )
        );
    }

    public function SameArtisteAction(Request $request, $type){
        $em = $this->getDoctrine()->getManager();

        if ($request->getMethod() == 'POST') {
            $artiste = $request->request->get('find');

            if ($type == 'news') {
                $query = $em->getRepository('MuzikSpiritBackBundle:News')->searchNewsLinkQuery($artiste);
            }elseif($type == 'clip'){
                $query = $em->getRepository('MuzikSpiritBackBundle:Clip')->searchClipLinkQuery($artiste);
            }
            $data = $query->setMaxResults(10)->getQuery()->getResult();


            $encoders = array(new XmlEncoder(), new JsonEncoder());
            $normalizers = array(new GetSetMethodNormalizer());

            $serializer = new Serializer($normalizers, $encoders);

            $data = $serializer->serialize($data, 'xml');

            $response = new Response();
            $response->setContent($data);
            $response->headers->set('Content-Type', 'application/xml');

            return $response;
        }

    }

    public function GetImageAction(Request $request,$type){
        $em = $this->getDoctrine()->getManager();

        if ($request->getMethod() == 'POST') {
            $find = $request->request->get('find');
            $query = $em->getRepository('MuzikSpiritBackBundle:Image')->searchImageByTypeQuery($find,$type);
            $data = $query->setMaxResults(1)->getQuery()->getResult();

            $encoders = array(new XmlEncoder(), new JsonEncoder());
            $normalizers = array(new GetSetMethodNormalizer());

            $serializer = new Serializer($normalizers, $encoders);

            $data = $serializer->serialize($data, 'xml');

            $response = new Response();
            $response->setContent($data);
            $response->headers->set('Content-Type', 'application/xml');

            return $response;
        }

    }
}