//import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Storage } from '@ionic/storage';
import { Platform } from 'ionic-angular';

@Injectable()
export class SplitPane 
{

  splitPaneState: boolean;
  //swipeBool: boolean;

  constructor(
  	public platform: Platform, 
  	public storage: Storage) 
  {
    this.storage.get('loginToken')
    .then((isToken) =>{
      this.splitPaneState = this.platform.width() > 900 ? (isToken ? true : false) : (isToken ? null : false);
      //this.swipeBool = isToken ? true : false;
    })
  }

  interuptSplitState(paramSplit)
  {
    this.splitPaneState = paramSplit;
  }
}
