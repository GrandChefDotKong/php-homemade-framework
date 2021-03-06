<?php
namespace App\Backend\Modules\News;

use \AdoFram\BackController;
use \AdoFram\HTTPRequest;
use \Entity\News;
use \Entity\Comment;
use \FormBuilder\CommentFormBuilder;
use \FormBuilder\NewsFormBuilder;
use \AdoFram\FormHandler;
use \AdoFram\BBCode;

class NewsController extends BackController {
	
	public function executeIndex(HTTPRequest $request) {
	  
		$this->page->addVar('title', 'Gestion des news');

		$manager = $this->managers->getManagerOf('News');

		$this->page->addVar('listeNews', $manager->getList());
		$this->page->addVar('nombreNews', $manager->count());
		
	}
	
	public function executeInsert(HTTPRequest $request) {
		
		$this->processForm($request);
    
		$this->page->addVar('title', 'Ajout d\'une news');
	
	}
	
	public function processForm(HTTPRequest $request) {
		
		if ($request->method() == 'POST') {
			
			$bbcode = new BBCode;
			$content = $bbcode->bbcodeToHtml($request->postData('contenu'));
		
			$news = new News([	
				'auteur' => $_SESSION['pseudo'],
				'titre' => $request->postData('titre'),
				'contenu' => $content,
			]);
    
			// L'identifiant de la news est transmis si on veut la modifier.
			if ($request->getExists('id')) {
				$news->setId($request->getData('id'));
			}
		}
		else {
			if($request->getExists('id')) {
				$news = $this->managers->getManagerOf('News')->getUnique($request->getData('id'));
			}
			else {
				$news = new News;
			}
		
		}
		
		$formBuilder = new NewsFormBuilder($news);
		$formBuilder->build();

		$form = $formBuilder->form();
		
		// On récupère le gestionnaire de formulaire (le paramètre de getManagerOf() est bien entendu à remplacer).
		$formHandler = new FormHandler($form, $this->managers->getManagerOf('News'), $request);

		if ($formHandler->process()) {
			$this->app->user()->setFlash($news->isNew() ? 'La news a bien été ajoutée !' : 'La news a bien été modifiée !');
			$this->app->httpResponse()->redirect('/admin/');
		}	

		$this->page->addVar('form', $form->createView());
	}
	
	public function executeUpdate(HTTPRequest $request) {
		
		$this->processForm($request);
    
		$this->page->addVar('title', 'Modification d\'une news');
	}
	
	public function executeDelete(HTTPRequest $request) {
		
		$newsId = $request->getData('id');
		
		$this->managers->getManagerOf('News')->delete($newsId);
		$this->managers->getManagerOf('Comments')->deleteFromNews($newsId);
    
		$this->app->user()->setFlash('La news a bien été supprimée !');
    
		$this->app->httpResponse()->redirect('.');
	}
	
	
	public function executeDeleteComment(HTTPRequest $request) {
		
		$this->managers->getManagerOf('Comments')->delete($request->getData('id'));
 
		$this->app->user()->setFlash('Le commentaire a bien été supprimé !');
 
		$this->app->httpResponse()->redirect('.');
	}
	
	public function executeUpdateComment(HTTPRequest $request) {
		
		$this->page->addVar('title', 'Modification d\'un commentaire');
 
		if ($request->method() == 'POST') {
			$comment = new Comment([
				'id' => $request->getData('id'),
				'auteur' => $_SESSION['pseudo'],
				'contenu' => $request->postData('contenu')
			]);
		}
		else {
			$comment = $this->managers->getManagerOf('Comments')->get($request->getData('id'));
		}
 
		$formBuilder = new CommentFormBuilder($comment);
		$formBuilder->build();
 
		$form = $formBuilder->form();
 
		$formHandler = new FormHandler($form, $this->managers->getManagerOf('Comments'), $request);
 
		if ($formHandler->process()) {
			$this->app->user()->setFlash('Le commentaire a bien été modifié');
			$this->app->httpResponse()->redirect('/admin/');
		}
 
		$this->page->addVar('form', $form->createView());
	}
}



