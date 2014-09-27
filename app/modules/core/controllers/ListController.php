<?php

namespace Mejili\Core\Controllers;

use Input, Response;
use Mejili\Core\Controllers\BaseController;
use Mejili\Core\Models\Board;
use Mejili\Core\Models\CardList;

/**
 * List Controller: Responisble for all operations
 * associated with lists
 * @author Masood Ahmed <masoodahm@live.com>
 */

class ListController extends BaseController {
    
    /**
	 * Add new list to the board and set the postion 
     * of the list to the max + 1 on the board
     * return success status and the id of the new list
	 * @return success:Boolean and id: int
	 */
    public function addList(){
        $boardid = Input::get('b');
        $title = Input::get('t');
        $board = Board::find($boardid);
        
        $list = new CardList();
        $list->title = $title;
        
        $maxPos = $board->lists()->max('position');
        $list->position = $maxPos + 1;
        $response['success'] = $board->lists()->save($list);
        $response['id'] = $list->id;
                
        return Response::json($response);
    }
    
    /**
	 * Update the position of the list and 
     * return true if successful else return false
	 * @return Boolean
	 */
    public function updatePosition(){
        $boardid = Input::get('b');
        $board = Board::find($boardid);
        $newPos = Input::get('np');
        $list = CardList::find(Input::get('lid'));
        $this->placeListAtPosition($board, $newPos, $list);
        
        $list->save();
        $this->reorganizeBoard($board);
        
        
        $response['success'] = true;
        return Response::json($response);
    }
    
    private function placeListAtPosition($board, $newPos, $list){
        if($list->position > $newPos){
            $this->makeSpaceTowardsLeft($board, $newPos);
            $list->position = $newPos - 2;
        }
        else{
            $this->makeSpaceTowardsRight($board, $newPos);
            $list->position = $newPos + 2 ;
        }
    }
    
    private function makeSpaceTowardsLeft($board, $pos){
        
        foreach($board->lists()->get() as $list){
            if($list->position <=   $pos){
                $list->position = $list->position - 1;
                $list->save();
            }
        }
    }
    
    private function makeSpaceTowardsRight($board, $pos){
        foreach($board->lists()->get() as $list){
            if($list->position >= $pos){
                $list->position = $list->position + 1;
                $list->save();
            }
        }
    }
    
    private function reorganizeBoard($board){
        $position=0;
        foreach ($board->lists()->orderby('position')->get() as $list ){
            $list->position = $position;            
            $list->save();
            $position++;
        }        
    }
}