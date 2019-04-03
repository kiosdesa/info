import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';
import { OrderComplaintPage } from './order-complaint';

@NgModule({
  declarations: [
    OrderComplaintPage,
  ],
  imports: [
    IonicPageModule.forChild(OrderComplaintPage),
    TranslateModule.forChild(), 
  ],
})
export class OrderComplaintPageModule {}
