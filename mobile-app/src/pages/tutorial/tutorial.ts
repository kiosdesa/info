import { Component } from '@angular/core';
import { IonicPage, MenuController, NavController, Platform } from 'ionic-angular';
import { Storage } from '@ionic/storage';
import { TranslateService } from '@ngx-translate/core';
import { SecondRunPage, HomeTab } from '../pages';
import { SplitPane } from '../../providers/providers';

export interface Slide {
  title: string;
  description: string;
  image: string;
}

@IonicPage()
@Component({
  selector: 'page-tutorial',
  templateUrl: 'tutorial.html'
})
export class TutorialPage 
{
  slides: Slide[];
  showSkip = true;
  dir: string = 'ltr';
  _setRootPoin: any;
  _setSplitBool: any;
  _setMenuBool: boolean = false;

  constructor(
    public navCtrl: NavController, 
    public menu: MenuController, 
    public translate: TranslateService, 
    public storage: Storage, 
    public platform: Platform,
    public splitState: SplitPane) 
  {
    this.dir = platform.dir();
    translate.get([
      "TUTORIAL_SLIDE1_TITLE",
      "TUTORIAL_SLIDE1_DESCRIPTION",
      "TUTORIAL_SLIDE2_TITLE",
      "TUTORIAL_SLIDE2_DESCRIPTION",
    ]).subscribe(
      (values) => {
        //console.log('Loaded values', values);
        this.slides = [
          {
            title: values.TUTORIAL_SLIDE1_TITLE,
            description: values.TUTORIAL_SLIDE1_DESCRIPTION,
            image: 'assets/img/tutslide-img-1.png',
          },
          {
            title: values.TUTORIAL_SLIDE2_TITLE,
            description: values.TUTORIAL_SLIDE2_DESCRIPTION,
            image: 'assets/img/tutslide-img-2.png',
          }
        ];
      });

      this.storage.get('loginToken')
      .then((isToken) => {
        if(isToken)
        {
          this._setSplitBool = this.platform.width() > 900 ? true : null;
          this._setMenuBool = true;
          this._setRootPoin = HomeTab;
        }
        else
        {
          this._setSplitBool = false;
          this._setRootPoin = SecondRunPage;
        }
      });
  }

  startApp() 
  {
    this.navCtrl.setRoot(this._setRootPoin, {}, {
      animate: true,
      direction: 'forward'
    })
    .then(() => {
      this.splitState.interuptSplitState(this._setSplitBool);
      this.menu.enable(this._setMenuBool, 'sideMainMenu');
      this.storage.set('hasSeenTutorial', 'true');
    });
  }

  onSlideChangeStart(slider) 
  {
    this.showSkip = !slider.isEnd();
  }
}
