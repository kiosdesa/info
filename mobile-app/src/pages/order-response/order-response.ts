import { Component } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { IonicPage, NavController, Events, ToastController, NavParams } from 'ionic-angular';
import { RequestApiProvider, Online } from './../../providers/providers';

export interface formFeedback
{
  invoice:string
  message:string
  score?:any
}

@IonicPage()
@Component({
  selector: 'page-order-response',
  templateUrl: 'order-response.html',
})
export class OrderResponsePage 
{
  _paramRoots: any;
  _formFeedback: formFeedback;
  _elementRateSeller: any;
  _defaultMessage: any;

  constructor(
    private api: RequestApiProvider, 
    private online: Online, 
    public translate: TranslateService, 
    public toast: ToastController, 
    public events: Events, 
    public navCtrl: NavController, 
    public navParams: NavParams) 
  {
    this._paramRoots = this.navParams.get('paramRoots');

    this._formFeedback = {
      invoice:undefined, message:undefined, score:[0,0]
    }

    this.translate.get([
      'ORDER_FEEDBACK_NOTE_ONE', 'ORDER_FEEDBACK_NOTE_TWO', 'ORDER_FEEDBACK_NOTE_THREE', 
      'ORDER_FEEDBACK_NOTE_FOUR', 'ORDER_FEEDBACK_NOTE_FIVE', 'ORDER_FEEDBACK_NOTE_SIX'
    ]).subscribe((val)=>{
      this._defaultMessage = [
        {text:val.ORDER_FEEDBACK_NOTE_ONE}, {text:val.ORDER_FEEDBACK_NOTE_TWO}, {text:val.ORDER_FEEDBACK_NOTE_THREE}, 
        {text:val.ORDER_FEEDBACK_NOTE_FOUR}, {text:val.ORDER_FEEDBACK_NOTE_FIVE}, {text:val.ORDER_FEEDBACK_NOTE_SIX}
      ];
    })

    this._elementRateSeller = [1,2,3,4,5]

    this.events.subscribe('star-rating:changed', (starRating) => {
      this._formFeedback.score[0] = starRating;
    })
  }

  ionViewDidLoad()
  {
    this.load() 
  }

  load()
  {
    this._formFeedback.invoice = this._paramRoots;
  }

  ratingSeller(val)
  {
    this._formFeedback.score[1] = val;
  }

  defaultMessage(p)
  {
    if(this._formFeedback.message == undefined)
    {
      this._formFeedback.message = p
    }
    else
    {
      this._formFeedback.message = this._formFeedback.message + ', ' + p
    }
  }

  sendFeedback()
  {
    if(this._formFeedback.message == undefined || this._formFeedback.score[0] == 0 || this._formFeedback.score[1] == 0)
    {
      this.toasting('Ups, empty')
    }
    else
    {
      if(this._formFeedback.message.length < 20)
      {
        this.toasting('Ups, message under 20 words')
      }
      else
      {
        const fields = this._formFeedback;
        const endpoint = 'feedbackOrders';
        this.api.post('transaction', endpoint, {init:'orders-feedback', pack:{fieldForm:fields}})
        .subscribe((res:any)=>{
          const Resp = res[endpoint];
          const colorToast = Resp.items.approve == true ? 'success-toast' : '';
          this.toasting(Resp.items.message, colorToast)
          if(Resp.items.approve == true)
          {
            setTimeout(()=>{
              this.navCtrl.pop().then(()=>{
                this.events.publish('switch:clicked',{segment:'done', status:1, shipping:[7]});
              })
            }, 3000)
          }
        },
        (err)=>{
          this.toasting('Ups, problem')
          this.online.checkOnline(true)
        })
      }
    }
    console.log(this._formFeedback);
  }

  toasting(message, color?:any)
  {
    const colors = color ? color : 'danger-toast';
    let toast = this.toast.create({
      message: message,
      cssClass: colors,
      duration: 5000,
      position: 'top'
    });
    toast.present();
  }
}