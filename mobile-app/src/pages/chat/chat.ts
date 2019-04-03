import { Component } from '@angular/core';
import { IonicPage, NavParams, NavController, MenuController } from 'ionic-angular';

@IonicPage()
@Component({
  selector: 'page-chat',
  templateUrl: 'chat.html'
})
export class ChatPage 
{ 
  _currentTitle: string;

  constructor( 
    public param: NavParams, 
    public menu: MenuController,
    public navCtrl: NavController) 
  {
    const params = this.param.get('paramRoots');
    this._currentTitle = params.title;
  }
}
